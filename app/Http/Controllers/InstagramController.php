<?php

namespace App\Http\Controllers;

use App\Models\InstagramAccount;
use App\Models\InstagramPost;
use App\Models\InstagramSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class InstagramController extends Controller
{
    /**
     * Exibe o painel de gerenciamento do Instagram.
     */
    public function index(Request $request)
    {
        try {
            $accounts = InstagramAccount::where('user_id', auth()->id())->get();
            $selectedAccountId = $request->get('account_id', optional($accounts->first())->id);
            $account = $accounts->where('id', $selectedAccountId)->first() ?: $accounts->first();

            $settings = null;
            try {
                $settings = InstagramSetting::firstOrCreate(['user_id' => auth()->id()]);
            } catch (\Throwable $tSettings) {
                Log::error('Erro ao obter InstagramSetting: ' . $tSettings->getMessage());
            }

            // Executa automaticamente a verificação de postagens agendadas vencidas
            try {
                Artisan::call('instagram:publish-scheduled');
            } catch (\Throwable $tSched) {
                Log::error('Erro ao executar agendamentos automáticos: ' . $tSched->getMessage());
            }

            $posts = InstagramPost::where('user_id', auth()->id())
                ->orderBy('created_at', 'desc')
                ->get();

            // Busca o Feed real de posts já publicados no perfil do Instagram com Cache de 5 min (evita travamento e requisições repetidas à Meta)
            $liveInstagramPosts = [];
            if ($account && $account->access_token && $account->instagram_account_id) {
                $cacheKey = "instagram_live_feed_" . auth()->id() . "_" . $account->instagram_account_id;
                
                $liveInstagramPosts = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($account) {
                    $postsData = [];
                    try {
                        $nextUrl = "https://graph.facebook.com/v19.0/{$account->instagram_account_id}/media?fields=id,caption,media_type,media_url,permalink,thumbnail_url,timestamp,like_count,comments_count,children{id,media_url,thumbnail_url,media_type}&limit=25&access_token=" . $account->access_token;
                        
                        $maxPages = 3; // Limita a 3 páginas (75 publicações recentes) para garantir carregamento instantâneo
                        $pageCount = 0;

                        while ($nextUrl && $pageCount < $maxPages) {
                            $feedResp = Http::withoutVerifying()->timeout(5)->get($nextUrl);
                            if ($feedResp->failed()) {
                                Log::error('Falha na resposta da Meta Graph API para feed do Instagram: ' . $feedResp->body());
                                break;
                            }

                            $data = $feedResp->json('data', []);
                            if (empty($data) || !is_array($data)) break;

                            $postsData = array_merge($postsData, $data);

                            $nextUrl = $feedResp->json('paging.next');
                            $pageCount++;
                        }
                    } catch (\Throwable $e) {
                        Log::error('Erro ao buscar feed vivo do Instagram: ' . $e->getMessage());
                    }
                    return $postsData;
                });

                $dbPostMap = $posts->pluck('id', 'instagram_media_id')->toArray();
                foreach ($liveInstagramPosts as &$item) {
                    $item['db_id'] = $dbPostMap[$item['id']] ?? null;

                    if (empty($item['media_url']) && !empty($item['children']['data'])) {
                        $firstChild = $item['children']['data'][0] ?? null;
                        if ($firstChild) {
                            $item['media_url'] = $firstChild['media_url'] ?? ($firstChild['thumbnail_url'] ?? null);
                        }
                    }
                }
            }

            return view('instagram.index', compact('accounts', 'account', 'posts', 'settings', 'liveInstagramPosts'));
        } catch (\Throwable $e) {
            Log::error('FATAL Error in InstagramController@index: ' . $e->getMessage() . ' | ' . $e->getTraceAsString());
            
            // Exibe o erro exato na tela para diagnosticar e resolver na Hostinger
            return response('<div style="padding:40px; font-family:sans-serif; background:#0f172a; color:#f87171; border-radius:12px; margin:40px;"><h2 style="color:#ef4444; margin-bottom:10px;">⚠️ Erro de Execução no Servidor (Instagram):</h2><p style="color:#f1f5f9; font-weight:bold; font-size:16px;">' . htmlspecialchars($e->getMessage()) . '</p><pre style="background:#1e293b; color:#94a3b8; padding:15px; border-radius:8px; overflow:auto; font-size:12px;">' . htmlspecialchars($e->getTraceAsString()) . '</pre></div>', 500);
        }
    }

    /**
     * Salva ou atualiza os ícones de sobreposição (Logo e Seta).
     */
    public function storeOverlayIcons(Request $request)
    {
        $request->validate([
            'logo' => 'nullable|image|max:5120',
            'logo_icon' => 'nullable|image|max:5120',
            'arrow' => 'nullable|image|max:5120',
            'arrow_icon' => 'nullable|image|max:5120',
        ]);

        $settings = InstagramSetting::firstOrCreate(['user_id' => auth()->id()]);

        $logoFile = $request->file('logo') ?: $request->file('logo_icon');
        if ($logoFile) {
            if ($settings->logo_path) {
                Storage::disk('public')->delete($settings->logo_path);
            }
            $settings->logo_path = $logoFile->store('instagram_overlays', 'public');
        }

        $arrowFile = $request->file('arrow') ?: $request->file('arrow_icon');
        if ($arrowFile) {
            if ($settings->arrow_path) {
                Storage::disk('public')->delete($settings->arrow_path);
            }
            $settings->arrow_path = $arrowFile->store('instagram_overlays', 'public');
        }

        $settings->save();

        return redirect()->route('instagram.index')->with('success', '✨ Ícones de sobreposição salvos com sucesso!');
    }

    /**
     * Inicia o fluxo de autorização do Meta / Instagram Graph API.
     */
    public function connect(Request $request)
    {
        $appId = trim(config('services.instagram.client_id') ?: env('INSTAGRAM_CLIENT_ID', ''));
        $appId = str_replace(['"', "'"], '', $appId);

        if (empty($appId)) {
            return redirect()->route('instagram.index')->with('error', 'O ID do aplicativo (INSTAGRAM_CLIENT_ID) não foi encontrado no arquivo .env ou a configuração está em cache. Execute "php artisan config:clear".');
        }

        $redirectUri = $this->getRedirectUri();
        $authMode = env('INSTAGRAM_AUTH_MODE', 'facebook'); // 'facebook' ou 'instagram'

        $customScopes = config('services.instagram.scopes') ?: env('INSTAGRAM_SCOPES');

        if ($customScopes) {
            $scopes = array_filter(array_map('trim', explode(',', $customScopes)));
        } elseif ($authMode === 'instagram') {
            $scopes = [
                'instagram_business_basic',
                'instagram_business_content_publish',
            ];
        } else {
            $scopes = [
                'public_profile',
                'instagram_basic',
                'instagram_content_publish',
                'instagram_manage_comments',
                'instagram_manage_messages',
                'pages_show_list',
                'pages_read_engagement',
                'pages_manage_posts',
                'pages_messaging',
                'business_management',
            ];
        }

        if ($authMode === 'facebook') {
            $scopes = array_map(function($s) {
                return str_replace(['instagram_business_basic', 'instagram_business_content_publish'], ['instagram_basic', 'instagram_content_publish'], $s);
            }, $scopes);
        }

        if ($authMode === 'instagram') {
            // Fluxo Direto do Instagram API
            $params = http_build_query([
                'client_id' => $appId,
                'redirect_uri' => $redirectUri,
                'response_type' => 'code',
                'scope' => implode(',', $scopes),
                'state' => csrf_token(),
            ]);
            $authUrl = "https://www.instagram.com/oauth/authorize?" . $params;
        } else {
            // Fluxo Padrão Meta Graph API via Login do Facebook para Empresas
            $params = http_build_query([
                'client_id' => $appId,
                'redirect_uri' => $redirectUri,
                'scope' => implode(',', $scopes),
                'response_type' => 'code',
                'state' => csrf_token(),
                'auth_type' => 'reauthenticate', // Força renovação total da sessão do Facebook
            ]);
            $authUrl = "https://www.facebook.com/v19.0/dialog/oauth?" . $params;
        }

        return redirect()->away($authUrl);
    }

    /**
     * Trata o retorno (callback) da autorização do Instagram/Facebook.
     */
    public function callback(Request $request)
    {
        if ($request->has('error') || !$request->has('code')) {
            $msg = $request->get('error_description', 'Autorização do Instagram cancelada ou não concluída.');
            return redirect()->route('instagram.index')->with('error', $msg);
        }

        try {
            $appId = trim(config('services.instagram.client_id') ?: env('INSTAGRAM_CLIENT_ID', ''));
            $appSecret = trim(config('services.instagram.client_secret') ?: env('INSTAGRAM_CLIENT_SECRET', ''));
            $appId = str_replace(['"', "'"], '', $appId);
            $appSecret = str_replace(['"', "'"], '', $appSecret);
            $redirectUri = $this->getRedirectUri();
            $code = $request->get('code');

            // 1. Troca o código via Meta Graph API (Facebook Login)
            $response = Http::asForm()->post('https://graph.facebook.com/v19.0/oauth/access_token', [
                'client_id' => $appId,
                'client_secret' => $appSecret,
                'redirect_uri' => $redirectUri,
                'code' => $code,
            ]);

            if ($response->failed()) {
                // Tenta fallback via GET
                $response = Http::get('https://graph.facebook.com/v19.0/oauth/access_token', [
                    'client_id' => $appId,
                    'client_secret' => $appSecret,
                    'redirect_uri' => $redirectUri,
                    'code' => $code,
                ]);
            }

            if ($response->failed()) {
                $err = $response->json('error.message', 'Erro ao obter token da Meta.');
                Log::error('Erro no token exchange do Facebook: ' . $response->body());
                return redirect()->route('instagram.index')->with('error', 'Erro ao autenticar com a Meta: ' . $err);
            }

            $shortToken = $response->json('access_token');

            // 2. Troca por Long-Lived Token (60 dias)
            $tokenResp = Http::get('https://graph.facebook.com/v19.0/oauth/access_token', [
                'grant_type' => 'fb_exchange_token',
                'client_id' => $appId,
                'client_secret' => $appSecret,
                'fb_exchange_token' => $shortToken,
            ]);

            $longToken = $tokenResp->json('access_token', $shortToken);

            // 3. Verifica as permissões concedidas e busca todas as páginas do Facebook com paginação completa
            $permResp = Http::get("https://graph.facebook.com/v19.0/me/permissions", ['access_token' => $longToken]);
            Log::info('Meta Granted Permissions: ', $permResp->json('data', []));

            $pages = [];
            $nextUrl = "https://graph.facebook.com/v19.0/me/accounts?fields=id,name,access_token,instagram_business_account{id,username,name,profile_picture_url},connected_instagram_account{id,username,name,profile_picture_url},page_backed_instagram_account{id,username,name,profile_picture_url}&limit=50&access_token=" . $longToken;

            while ($nextUrl) {
                $pagesResp = Http::get($nextUrl);
                if ($pagesResp->failed()) break;

                $data = $pagesResp->json('data', []);
                $pages = array_merge($pages, $data);

                $nextUrl = $pagesResp->json('paging.next');
            }

            // Busca também páginas gerenciadas via Portfólio Empresarial (Business Manager)
            $bizResp = Http::get("https://graph.facebook.com/v19.0/me/businesses", [
                'access_token' => $longToken,
                'fields' => 'id,name,pages{id,name,access_token,instagram_business_account{id,username,name,profile_picture_url},connected_instagram_account{id,username,name,profile_picture_url},page_backed_instagram_account{id,username,name,profile_picture_url}},client_pages{id,name,access_token,instagram_business_account{id,username,name,profile_picture_url},connected_instagram_account{id,username,name,profile_picture_url},page_backed_instagram_account{id,username,name,profile_picture_url}}'
            ]);

            if ($bizResp->successful()) {
                $businesses = $bizResp->json('data', []);
                foreach ($businesses as $biz) {
                    $bizPages = array_merge(
                        $biz['pages']['data'] ?? [],
                        $biz['client_pages']['data'] ?? []
                    );
                    foreach ($bizPages as $bp) {
                        if (!collect($pages)->pluck('id')->contains($bp['id'])) {
                            $pages[] = $bp;
                        }
                    }
                }
            }

            // Busca explicitamente a página Danilo Miguel - Design & Ilustração (ID 667853480008070) se omitida
            if (!collect($pages)->pluck('id')->contains('667853480008070')) {
                $directPageResp = Http::get("https://graph.facebook.com/v19.0/667853480008070", [
                    'access_token' => $longToken,
                    'fields' => 'id,name,access_token,instagram_business_account{id,username,name,profile_picture_url},connected_instagram_account{id,username,name,profile_picture_url}'
                ]);

                if ($directPageResp->successful() && $directPageResp->json('id')) {
                    $pages[] = $directPageResp->json();
                }
            }

            Log::info('Meta Facebook Pages returned: ', $pages);

            $connectedCount = 0;
            $lastUsername = '';
            $pageNames = [];

            foreach ($pages as $page) {
                $pageId = $page['id'] ?? null;
                if (!$pageId) continue;
                $pageNames[] = $page['name'] ?? "Página ID {$pageId}";

                $igAccountData = $page['instagram_business_account'] 
                    ?? $page['connected_instagram_account'] 
                    ?? $page['page_backed_instagram_account'] 
                    ?? null;
                $pageAccessToken = $page['access_token'] ?? $longToken;

                if (!$igAccountData) {
                    // Tenta buscar a conta do instagram usando o Token de Acesso da própria Página
                    $pageDetailResp = Http::get("https://graph.facebook.com/v19.0/{$pageId}", [
                        'fields' => 'instagram_business_account{id,username,name,profile_picture_url},connected_instagram_account{id,username,name,profile_picture_url},page_backed_instagram_account{id,username,name,profile_picture_url}',
                        'access_token' => $pageAccessToken,
                    ]);

                    if ($pageDetailResp->successful()) {
                        $json = $pageDetailResp->json();
                        Log::info("Meta Page {$pageId} details: ", $json);
                        $igAccountData = $json['instagram_business_account']
                            ?? $json['connected_instagram_account']
                            ?? $json['page_backed_instagram_account']
                            ?? null;
                    }
                }

                if ($igAccountData) {
                    $lastUsername = $igAccountData['username'] ?? '';
                    InstagramAccount::updateOrCreate(
                        ['instagram_account_id' => $igAccountData['id']],
                        [
                            'user_id' => auth()->id(),
                            'facebook_page_id' => $pageId,
                            'username' => $igAccountData['username'] ?? 'instagram_user',
                            'name' => $igAccountData['name'] ?? null,
                            'profile_picture_url' => $igAccountData['profile_picture_url'] ?? null,
                            'access_token' => $longToken,
                            'token_expires_at' => now()->addDays(60),
                            'is_active' => true,
                        ]
                    );
                    $connectedCount++;
                }
            }

            // Fallback direto no me?fields=instagram_accounts se nenhuma página reportou conta
            if ($connectedCount === 0) {
                $meIgResp = Http::get("https://graph.facebook.com/v19.0/me", [
                    'access_token' => $longToken,
                    'fields' => 'instagram_accounts{id,username,name,profile_picture_url}'
                ]);

                $directIgAccounts = $meIgResp->json('instagram_accounts.data', []);
                foreach ($directIgAccounts as $igAccountData) {
                    $lastUsername = $igAccountData['username'] ?? '';
                    InstagramAccount::updateOrCreate(
                        ['instagram_account_id' => $igAccountData['id']],
                        [
                            'user_id' => auth()->id(),
                            'facebook_page_id' => null,
                            'username' => $igAccountData['username'] ?? 'instagram_user',
                            'name' => $igAccountData['name'] ?? null,
                            'profile_picture_url' => $igAccountData['profile_picture_url'] ?? null,
                            'access_token' => $longToken,
                            'token_expires_at' => now()->addDays(60),
                            'is_active' => true,
                        ]
                    );
                    $connectedCount++;
                }
            }

            if ($connectedCount > 0) {
                $msg = $connectedCount === 1 
                    ? '🎉 Conta do Instagram @' . $lastUsername . ' conectada com sucesso!' 
                    : '🎉 ' . $connectedCount . ' contas do Instagram conectadas com sucesso!';
                return redirect()->route('instagram.index')->with('success', $msg);
            }

            $pageSummary = count($pageNames) > 0 ? ' (Páginas encontradas: ' . implode(', ', $pageNames) . ')' : ' (Nenhuma página do Facebook encontrada)';
            $rawDebug = ' Resposta bruta da Meta para a página: ' . json_encode($pages, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

            return redirect()->route('instagram.index')->with('error', 'Nenhuma conta profissional do Instagram conectada foi encontrada' . $pageSummary . '.' . $rawDebug);

        } catch (\Exception $e) {
            Log::error('Erro no callback do Instagram: ' . $e->getMessage());
            return redirect()->route('instagram.index')->with('error', 'Falha na integração com o Instagram: ' . $e->getMessage());
        }
    }

    /**
     * Desconecta a conta do Instagram.
     */
    public function disconnect(InstagramAccount $account)
    {
        abort_if($account->user_id !== auth()->id(), 403);
        $account->delete();

        return redirect()->route('instagram.index')->with('info', 'Conta do Instagram desconectada.');
    }

    /**
     * Cadastra ou agenda a postagem (Feed Único, Carrossel ou Story).
     */
    public function storePost(Request $request)
    {
        try {
            $request->validate([
                'instagram_account_id' => 'nullable|exists:instagram_accounts,id',
                'media_type' => 'required|in:IMAGE,CAROUSEL,STORY',
                'category' => 'nullable|string',
                'caption' => 'nullable|string',
                'has_logo_overlay' => 'nullable|boolean',
                'has_arrow_overlay' => 'nullable|boolean',
                'post_to_facebook' => 'nullable|boolean',
                'action' => 'required|in:now,schedule',
                'scheduled_at' => 'nullable|required_if:action,schedule|date',
            ], [
                'scheduled_at.required_if' => 'Informe a data e o horário para agendar a postagem.',
            ]);

            $accountId = $request->input('instagram_account_id');
            $account = $accountId 
                ? InstagramAccount::where('id', $accountId)->where('user_id', auth()->id())->first()
                : InstagramAccount::where('user_id', auth()->id())->where('is_active', true)->first();

            if (!$account) {
                return redirect()->route('instagram.index')->with('error', 'Conecte sua conta do Instagram antes de publicar.');
            }

            $mediaType = $request->input('media_type', 'IMAGE');
            $hasLogo = $request->boolean('has_logo_overlay');
            $hasArrow = $request->boolean('has_arrow_overlay');
            $postToFacebook = $request->boolean('post_to_facebook');
            $category = $request->input('category', 'Geral');

            $mainPath = null;
            $mediaUrls = [];

            // Coleta TODAS as mídias enviadas na requisição independente da chave do formulário
            $uploadedFiles = [];
            foreach ($request->allFiles() as $fileGroup) {
                if (is_array($fileGroup)) {
                    foreach ($fileGroup as $f) {
                        if ($f instanceof \Illuminate\Http\UploadedFile && $f->isValid()) {
                            $uploadedFiles[] = $f;
                        }
                    }
                } elseif ($fileGroup instanceof \Illuminate\Http\UploadedFile && $fileGroup->isValid()) {
                    $uploadedFiles[] = $fileGroup;
                }
            }

            if ($mediaType === 'CAROUSEL') {
                if (count($uploadedFiles) < 2) {
                    return redirect()->back()->withInput()->with('error', 'Selecione pelo menos 2 imagens para criar um Carrossel.');
                }

                $totalFiles = count($uploadedFiles);
                foreach ($uploadedFiles as $idx => $imgFile) {
                    $rawPath = $imgFile->store('instagram_posts', 'public');
                    // A seta (indicador de deslizar) só é aplicada se NÃO for o último slide do carrossel
                    $shouldApplyArrow = $hasArrow && ($idx < ($totalFiles - 1));
                    $processedPath = $this->applyOverlays($rawPath, $hasLogo, $shouldApplyArrow);
                    $mediaUrls[] = $processedPath;
                }
                $mainPath = $mediaUrls[0] ?? null;
            } else {
                $singleFile = $uploadedFiles[0] ?? null;
                if ($singleFile) {
                    $rawPath = $singleFile->store('instagram_posts', 'public');
                    $mainPath = $this->applyOverlays($rawPath, $hasLogo, $hasArrow);
                } else {
                    return redirect()->back()->withInput()->with('error', 'Selecione uma imagem para a publicação.');
                }
            }

            $post = InstagramPost::create([
                'user_id' => auth()->id(),
                'instagram_account_id' => $account->id,
                'media_type' => $mediaType,
                'category' => $category,
                'media_path' => $mainPath,
                'media_urls' => $mediaUrls,
                'caption' => $request->caption,
                'has_logo_overlay' => $hasLogo,
                'has_arrow_overlay' => $hasArrow,
                'status' => $request->action === 'now' ? 'rascunho' : 'agendado',
                'post_to_facebook' => $postToFacebook,
                'scheduled_at' => $request->action === 'schedule' ? Carbon::parse($request->scheduled_at, config('app.timezone', 'America/Sao_Paulo')) : null,
            ]);

            if ($request->action === 'now') {
                if ($mediaType === 'CAROUSEL') {
                    return $this->publishCarouselPostToInstagram($post);
                } elseif ($mediaType === 'STORY') {
                    return $this->publishStoryPostToInstagram($post);
                } else {
                    return $this->publishPostToInstagram($post);
                }
            }

            $schedFormatted = Carbon::parse($request->scheduled_at, config('app.timezone', 'America/Sao_Paulo'))->format('d/m/Y H:i');
            return redirect()->route('instagram.index')->with('success', '🗓️ Conteúdo agendado com sucesso para ' . $schedFormatted);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Erro em storePost: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return redirect()->back()->withInput()->with('error', 'Erro ao processar postagem: ' . $e->getMessage());
        }
    }

    /**
     * Atualiza dados de uma postagem agendada (legenda, data/horário de agendamento ou publicação imediata).
     */
    public function updatePost(Request $request, $id)
    {
        $post = InstagramPost::where('user_id', auth()->id())->findOrFail($id);

        $request->validate([
            'caption' => 'nullable|string',
            'scheduled_at' => 'nullable|date',
            'publish_now' => 'nullable',
        ]);

        if ($request->has('caption')) {
            $post->caption = $request->caption;
        }

        if ($request->boolean('publish_now')) {
            $post->status = 'rascunho';
            $post->save();

            if ($post->media_type === 'CAROUSEL') {
                return $this->publishCarouselPostToInstagram($post);
            } elseif ($post->media_type === 'STORY') {
                return $this->publishStoryPostToInstagram($post);
            } else {
                return $this->publishPostToInstagram($post);
            }
        }

        if ($request->filled('scheduled_at')) {
            $post->scheduled_at = Carbon::parse($request->scheduled_at);
            $post->status = 'agendado';
            $post->error_message = null;
        }

        $post->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Postagem agendada atualizada com sucesso!',
                'post' => $post
            ]);
        }

        return redirect()->route('instagram.index', ['tab' => 'calendario'])->with('success', '✏️ Postagem agendada atualizada com sucesso!');
    }

    /**
     * Exclui / cancela postagem do banco de dados e da API da Meta se publicada.
     */
    public function destroyPost($id)
    {
        $post = InstagramPost::where('user_id', auth()->id())
            ->where(function($q) use ($id) {
                $q->where('id', $id)->orWhere('instagram_media_id', $id);
            })->first();

        $account = InstagramAccount::where('user_id', auth()->id())->first();

        $mediaIdToDelete = $post ? $post->instagram_media_id : (is_numeric($id) && strlen($id) > 10 ? $id : null);
        if (!$mediaIdToDelete && is_string($id) && strlen($id) > 10) {
            $mediaIdToDelete = $id;
        }

        $accessToken = $post && $post->instagramAccount 
            ? $post->instagramAccount->access_token 
            : ($account ? $account->access_token : null);

        $metaDeleted = false;
        $metaErrorMsg = null;

        if ($mediaIdToDelete && $accessToken) {
            try {
                $res = Http::delete("https://graph.facebook.com/v19.0/{$mediaIdToDelete}", [
                    'access_token' => $accessToken,
                ]);

                if ($res->successful()) {
                    $metaDeleted = true;
                } else {
                    $metaErrorMsg = $res->json('error.message', 'O Instagram proíbe a exclusão de mídias já publicadas através da API externa.');
                    Log::info('Meta API Delete Response:', $res->json());
                }
            } catch (\Exception $e) {
                Log::error('Erro ao excluir mídia na API do Instagram: ' . $e->getMessage());
            }
        }

        if ($post) {
            if ($post->media_path) {
                Storage::disk('public')->delete($post->media_path);
            }
            $post->delete();
        }

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'meta_deleted' => $metaDeleted,
                'meta_error' => $metaErrorMsg,
                'message' => $metaDeleted ? 'Postagem excluída com sucesso.' : ($metaErrorMsg ?: 'Postagem removida do sistema local.'),
                'media_id' => $mediaIdToDelete,
                'db_id' => $post ? $post->id : null
            ]);
        }

        $tab = request()->get('tab', 'feed_real');
        $msgType = $metaDeleted ? 'success' : 'info';
        $msgContent = $metaDeleted ? 'Postagem excluída com sucesso do Instagram!' : 'Nota: Por políticas de segurança da Meta, posts já publicados no perfil não podem ser apagados via API externa. Apague diretamente no aplicativo do Instagram.';

        return redirect()->route('instagram.index', ['tab' => $tab])->with($msgType, $msgContent);
    }

    /**
     * Publica uma Imagem Única no Feed do Instagram.
     */
    public function publishPostToInstagram(InstagramPost $post)
    {
        try {
            $account = $post->instagramAccount;
            if (!$account) throw new \Exception('Conta do Instagram não encontrada.');

            $publicImageUrl = $this->getPublicImageUrl($post->media_path);

            $containerResp = Http::post("https://graph.facebook.com/v19.0/{$account->instagram_account_id}/media", [
                'image_url' => $publicImageUrl,
                'caption' => $post->caption,
                'access_token' => $account->access_token,
            ]);

            if ($containerResp->failed()) {
                $err = $containerResp->json('error.message', 'Erro ao enviar imagem ao Instagram.');
                Log::error('Erro container Instagram: ' . $containerResp->body());
                $post->update(['status' => 'erro', 'error_message' => $err]);
                return redirect()->route('instagram.index')->with('error', 'Erro ao enviar para o Instagram: ' . $err);
            }

            $containerId = $containerResp->json('id');
            $this->waitForMetaContainerReady($containerId, $account->access_token);

            $publishResp = Http::post("https://graph.facebook.com/v19.0/{$account->instagram_account_id}/media_publish", [
                'creation_id' => $containerId,
                'access_token' => $account->access_token,
            ]);

            if ($publishResp->failed()) {
                $err = $publishResp->json('error.message', 'Erro ao publicar no Instagram.');
                Log::error('Erro publicar Instagram: ' . $publishResp->body());
                $post->update(['status' => 'erro', 'error_message' => $err]);
                return redirect()->route('instagram.index')->with('error', 'Erro ao publicar no Instagram: ' . $err);
            }

            $post->update([
                'status' => 'publicado',
                'published_at' => now(),
                'instagram_media_id' => $publishResp->json('id'),
                'error_message' => null,
            ]);

            return redirect()->route('instagram.index')->with('success', '🚀 Post publicado com sucesso no Instagram!');

        } catch (\Exception $e) {
            Log::error('Exceção ao publicar no Instagram: ' . $e->getMessage());
            $post->update(['status' => 'erro', 'error_message' => $e->getMessage()]);
            return redirect()->route('instagram.index')->with('error', 'Falha na publicação: ' . $e->getMessage());
        }
    }

    /**
     * Publica um Carrossel com múltiplas imagens no Instagram.
     */
    public function publishCarouselPostToInstagram(InstagramPost $post)
    {
        try {
            $account = $post->instagramAccount;
            if (!$account) throw new \Exception('Conta do Instagram não encontrada.');

            $itemContainerIds = [];
            $mediaUrls = $post->media_urls ?: [$post->media_path];

            foreach ($mediaUrls as $path) {
                $publicUrl = $this->getPublicImageUrl($path);
                $itemResp = Http::post("https://graph.facebook.com/v19.0/{$account->instagram_account_id}/media", [
                    'image_url' => $publicUrl,
                    'is_carousel_item' => 'true',
                    'access_token' => $account->access_token,
                ]);

                if ($itemResp->failed()) {
                    $err = $itemResp->json('error.message', 'Erro ao enviar item do carrossel ao Instagram.');
                    Log::error('Erro item carrossel: ' . $itemResp->body());
                    $post->update(['status' => 'erro', 'error_message' => $err]);
                    return redirect()->route('instagram.index')->with('error', 'Erro no carrossel: ' . $err);
                }

                $childId = $itemResp->json('id');
                $this->waitForMetaContainerReady($childId, $account->access_token);
                $itemContainerIds[] = $childId;
            }

            // Criar container pai do Carrossel
            $parentResp = Http::post("https://graph.facebook.com/v19.0/{$account->instagram_account_id}/media", [
                'media_type' => 'CAROUSEL',
                'caption' => $post->caption,
                'children' => implode(',', $itemContainerIds),
                'access_token' => $account->access_token,
            ]);

            if ($parentResp->failed()) {
                $err = $parentResp->json('error.message', 'Erro ao criar container pai do carrossel.');
                Log::error('Erro container pai carrossel: ' . $parentResp->body());
                $post->update(['status' => 'erro', 'error_message' => $err]);
                return redirect()->route('instagram.index')->with('error', 'Erro ao criar carrossel: ' . $err);
            }

            $parentId = $parentResp->json('id');
            $this->waitForMetaContainerReady($parentId, $account->access_token);

            // Publicar
            $publishResp = Http::post("https://graph.facebook.com/v19.0/{$account->instagram_account_id}/media_publish", [
                'creation_id' => $parentId,
                'access_token' => $account->access_token,
            ]);

            if ($publishResp->failed()) {
                $err = $publishResp->json('error.message', 'Erro ao publicar carrossel no Instagram.');
                $post->update(['status' => 'erro', 'error_message' => $err]);
                return redirect()->route('instagram.index')->with('error', 'Erro na publicação do carrossel: ' . $err);
            }

            $post->update([
                'status' => 'publicado',
                'published_at' => now(),
                'instagram_media_id' => $publishResp->json('id'),
                'error_message' => null,
            ]);

            return redirect()->route('instagram.index')->with('success', '🎡 Carrossel publicado com sucesso no Instagram!');

        } catch (\Exception $e) {
            Log::error('Exceção ao publicar carrossel: ' . $e->getMessage());
            $post->update(['status' => 'erro', 'error_message' => $e->getMessage()]);
            return redirect()->route('instagram.index')->with('error', 'Falha no carrossel: ' . $e->getMessage());
        }
    }

    /**
     * Publica um Story no Instagram.
     */
    public function publishStoryPostToInstagram(InstagramPost $post)
    {
        try {
            $account = $post->instagramAccount;
            if (!$account) throw new \Exception('Conta do Instagram não encontrada.');

            $publicImageUrl = $this->getPublicImageUrl($post->media_path);

            $containerResp = Http::post("https://graph.facebook.com/v19.0/{$account->instagram_account_id}/media", [
                'image_url' => $publicImageUrl,
                'media_type' => 'STORIES',
                'access_token' => $account->access_token,
            ]);

            if ($containerResp->failed()) {
                $err = $containerResp->json('error.message', 'Erro ao enviar Story para o Instagram.');
                Log::error('Erro container Story: ' . $containerResp->body());
                $post->update(['status' => 'erro', 'error_message' => $err]);
                return redirect()->route('instagram.index')->with('error', 'Erro ao enviar Story: ' . $err);
            }

            $containerId = $containerResp->json('id');

            $publishResp = Http::post("https://graph.facebook.com/v19.0/{$account->instagram_account_id}/media_publish", [
                'creation_id' => $containerId,
                'access_token' => $account->access_token,
            ]);

            if ($publishResp->failed()) {
                $err = $publishResp->json('error.message', 'Erro ao publicar Story.');
                $post->update(['status' => 'erro', 'error_message' => $err]);
                return redirect()->route('instagram.index')->with('error', 'Erro ao publicar Story: ' . $err);
            }

            $post->update([
                'status' => 'publicado',
                'published_at' => now(),
                'instagram_media_id' => $publishResp->json('id'),
                'error_message' => null,
            ]);

            return redirect()->route('instagram.index')->with('success', '📸 Story publicado com sucesso no Instagram!');

        } catch (\Exception $e) {
            Log::error('Exceção ao publicar Story: ' . $e->getMessage());
            $post->update(['status' => 'erro', 'error_message' => $e->getMessage()]);
            return redirect()->route('instagram.index')->with('error', 'Falha no Story: ' . $e->getMessage());
        }
    }

    /**
     * Aplica os ícones de marca d'água (Logo no topo e Seta no rodapé) na imagem enviada.
     */
    protected function applyOverlays($relativePath, $hasLogo, $hasArrow)
    {
        if (!$hasLogo && !$hasArrow) return $relativePath;

        $fullPath = storage_path('app/public/' . $relativePath);
        if (!file_exists($fullPath)) return $relativePath;

        $info = @getimagesize($fullPath);
        if (!$info) return $relativePath;

        $mime = $info['mime'];
        switch ($mime) {
            case 'image/jpeg': $srcImg = @imagecreatefromjpeg($fullPath); break;
            case 'image/png':  $srcImg = @imagecreatefrompng($fullPath); break;
            case 'image/webp': $srcImg = @imagecreatefromwebp($fullPath); break;
            default: return $relativePath;
        }

        if (!$srcImg) return $relativePath;

        $width = imagesx($srcImg);
        $height = imagesy($srcImg);

        imagealphablending($srcImg, true);

        $settings = InstagramSetting::where('user_id', auth()->id())->first();

        // 1. Logo Overlay (Topo Direita)
        if ($hasLogo && $settings && $settings->logo_path && file_exists(storage_path('app/public/' . $settings->logo_path))) {
            $logoFullPath = storage_path('app/public/' . $settings->logo_path);
            $logoInfo = @getimagesize($logoFullPath);
            if ($logoInfo) {
                $logoMime = $logoInfo['mime'];
                $logoSrc = ($logoMime === 'image/png') 
                    ? @imagecreatefrompng($logoFullPath) 
                    : (($logoMime === 'image/webp') ? @imagecreatefromwebp($logoFullPath) : @imagecreatefromjpeg($logoFullPath));

                if ($logoSrc) {
                    $logoW = imagesx($logoSrc);
                    $logoH = imagesy($logoSrc);

                    $targetW = (int)($width * 0.18); // 18% da largura da imagem
                    $targetH = (int)($logoH * ($targetW / max(1, $logoW)));
                    $posX = $width - $targetW - (int)($width * 0.04);
                    $posY = (int)($height * 0.04);

                    $logoResized = imagecreatetruecolor($targetW, $targetH);
                    imagealphablending($logoResized, false);
                    imagesavealpha($logoResized, true);
                    $transparent = imagecolorallocatealpha($logoResized, 0, 0, 0, 127);
                    imagefilledrectangle($logoResized, 0, 0, $targetW, $targetH, $transparent);
                    imagecopyresampled($logoResized, $logoSrc, 0, 0, 0, 0, $targetW, $targetH, $logoW, $logoH);

                    imagealphablending($srcImg, true);
                    imagecopy($srcImg, $logoResized, $posX, $posY, 0, 0, $targetW, $targetH);
                    imagedestroy($logoResized);
                    imagedestroy($logoSrc);
                }
            }
        }

        // 2. Arrow Overlay (Rodapé Direita - Arraste pro lado / Seta)
        if ($hasArrow && $settings && $settings->arrow_path && file_exists(storage_path('app/public/' . $settings->arrow_path))) {
            $arrowFullPath = storage_path('app/public/' . $settings->arrow_path);
            $arrowInfo = @getimagesize($arrowFullPath);
            if ($arrowInfo) {
                $arrowMime = $arrowInfo['mime'];
                $arrowSrc = ($arrowMime === 'image/png')
                    ? @imagecreatefrompng($arrowFullPath)
                    : (($arrowMime === 'image/webp') ? @imagecreatefromwebp($arrowFullPath) : @imagecreatefromjpeg($arrowFullPath));

                if ($arrowSrc) {
                    $arrowW = imagesx($arrowSrc);
                    $arrowH = imagesy($arrowSrc);

                    $targetW = (int)($width * 0.14); // 14% da largura da imagem
                    $targetH = (int)($arrowH * ($targetW / max(1, $arrowW)));
                    $posX = $width - $targetW - (int)($width * 0.05);
                    $posY = $height - $targetH - (int)($height * 0.05);

                    $arrowResized = imagecreatetruecolor($targetW, $targetH);
                    imagealphablending($arrowResized, false);
                    imagesavealpha($arrowResized, true);
                    $transparent = imagecolorallocatealpha($arrowResized, 0, 0, 0, 127);
                    imagefilledrectangle($arrowResized, 0, 0, $targetW, $targetH, $transparent);
                    imagecopyresampled($arrowResized, $arrowSrc, 0, 0, 0, 0, $targetW, $targetH, $arrowW, $arrowH);

                    imagealphablending($srcImg, true);
                    imagecopy($srcImg, $arrowResized, $posX, $posY, 0, 0, $targetW, $targetH);
                    imagedestroy($arrowResized);
                    imagedestroy($arrowSrc);
                }
            }
        }

        // Salva imagem modificada
        $newFilename = 'instagram_posts/overlay_' . time() . '_' . uniqid() . '.jpg';
        $savePath = storage_path('app/public/' . $newFilename);
        imagejpeg($srcImg, $savePath, 92);
        imagedestroy($srcImg);

        return $newFilename;
    }

    /**
     * Gerador Inteligente de Hashtags baseado no texto da Legenda.
     */
    public function generateAiHashtags(Request $request)
    {
        $caption = strtolower($request->input('caption', ''));

        // Dicionário de tópicos e hashtags de alto alcance
        $topicsMap = [
            'design' => ['#designgrafico', '#identidadevisual', '#logodesign', '#designbr', '#designer', '#branding', '#designgraficobr', '#creative', '#graphicdesign', '#visualidentity', '#logoinspiration', '#graficodesign'],
            'logo' => ['#logodesign', '#logoinspiration', '#logotipo', '#marcadagua', '#marca', '#logotype', '#brandingdesign', '#brandidentity', '#vectorlogo'],
            'marca' => ['#branding', '#identidadevisual', '#estrategaidebrand', '#posicionamento', '#branddesign', '#brandidentity', '#marcaforte', '#brandingdesign'],
            'freela' => ['#freelancerbr', '#freelance', '#gestordefreelas', '#vidadefreela', '#trabalhoremoto', '#carreiradesign', '#freelancerlife', '#homeofficebr', '#designindependente'],
            'social' => ['#socialmedia', '#marketingdigital', '#midiasociais', '#gestordesocialmedia', '#conteudodigital', '#engajamento', '#instagramdicas', '#estrategiadeconteudo'],
            'marketing' => ['#marketingdigital', '#mkt', '#estrategiademarketing', '#vendas', '#empreendedorismo', '#marketingconteudo', '#crescimento', '#tráfegopago'],
            'arte' => ['#artedigital', '#ilustracao', '#procreate', '#illustrator', '#desenhodigital', '#vectorart', '#digitalart', '#ilustra', '#artebr'],
            'feed' => ['#feedorganizado', '#carrossel', '#conteudodigital', '#postdesign', '#reelsbrasil', '#dicasdedesign', '#esteticafeed'],
            'foto' => ['#fotografia', '#ensaiofotografico', '#edicaodefoto', '#lightroom', '#photoshop', '#fotografiademarketing'],
            'web' => ['#webdesign', '#uiux', '#uidesign', '#uxdesign', '#site', '#wordpress', '#elementor', '#figmadesign'],
        ];

        // Hashtags genéricas de alta engajamento
        $generalTrending = [
            '#viral', '#dicas', '#emalta', '#criatividade', '#portfoliodesign', '#inspiração',
            '#sucesso', '#negócios', '#foco', '#inovacao', '#empreendedordigital', '#conhecimento',
            '#estudantededesign', '#designersdobrasil', '#tecnologia', '#brasil', '#tendencia2026', '#conteudodevalor'
        ];

        $matchedTags = [];

        // Verifica palavras-chave na legenda
        foreach ($topicsMap as $keyword => $tags) {
            if (str_contains($caption, $keyword)) {
                $matchedTags = array_merge($matchedTags, $tags);
            }
        }

        // Se houver poucas combinações diretas, inclui tópicos chave de design e freela
        if (count($matchedTags) < 15) {
            $matchedTags = array_merge($matchedTags, $topicsMap['design'], $topicsMap['freela'], $topicsMap['social']);
        }

        // Adiciona as hashtags em alta até completar 30
        $matchedTags = array_merge($matchedTags, $generalTrending);
        $finalHashtags = array_slice(array_unique($matchedTags), 0, 30);

        return response()->json([
            'success' => true,
            'count' => count($finalHashtags),
            'hashtags' => array_values($finalHashtags),
            'formatted' => implode(' ', $finalHashtags),
        ]);
    }

    /**
     * Salva um novo tema de hashtags personalizado.
     */
    public function saveHashtagTheme(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'hashtags' => 'required|string',
        ]);

        $settings = InstagramSetting::firstOrCreate(['user_id' => auth()->id()]);
        $themes = $settings->saved_themes ?: [];

        // Limpa e formata as hashtags
        $rawTags = preg_split('/[\s,]+/', $request->hashtags);
        $cleanTags = array_filter(array_map(function($t) {
            $t = trim($t);
            if (!$t) return null;
            return str_starts_with($t, '#') ? $t : '#' . $t;
        }, $rawTags));

        $themes[] = [
            'name' => $request->name,
            'hashtags' => array_values(array_unique($cleanTags)),
            'created_at' => now()->format('d/m/Y'),
        ];

        $settings->saved_themes = $themes;
        $settings->save();

        return response()->json([
            'success' => true,
            'message' => 'Tema salvo com sucesso!',
            'themes' => $themes,
        ]);
    }

    /**
     * Salva um novo tema de hashtags personalizado.
     */
    public function deleteHashtagTheme(Request $request, $index)
    {
        $settings = InstagramSetting::firstOrCreate(['user_id' => auth()->id()]);
        $themes = $settings->saved_themes ?: [];

        if (isset($themes[$index])) {
            array_splice($themes, $index, 1);
            $settings->saved_themes = $themes;
            $settings->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Tema excluído!',
            'themes' => $themes,
        ]);
    }

    /**
     * Retorna a URL pública acessível pela Meta / Instagram Graph API.
     * Se estiver em ambiente local (localhost / .test), faz fallback upload para temp host público.
     */
    protected function getPublicImageUrl($relativePath)
    {
        $cleanPath = ltrim($relativePath, '/');
        $localUrl = asset('storage/' . $cleanPath);

        // Força HTTPS se não for localhost
        $host = parse_url($localUrl, PHP_URL_HOST) ?? '';
        $isLocal = str_contains($host, 'localhost') || str_contains($host, '127.0.0.1') || str_contains($host, '.test');

        if (!$isLocal && str_starts_with($localUrl, 'http://')) {
            $localUrl = str_replace('http://', 'https://', $localUrl);
        }

        // Se houver PUBLIC_URL configurado no .env (ex: Ngrok ou subdomínio público)
        $publicBase = env('PUBLIC_URL');
        if ($publicBase && !str_contains($publicBase, 'localhost') && !str_contains($publicBase, '127.0.0.1') && !str_contains($publicBase, '.test')) {
            $url = rtrim($publicBase, '/') . '/storage/' . $cleanPath;
            if (str_starts_with($url, 'http://')) {
                $url = str_replace('http://', 'https://', $url);
            }
            return $url;
        }

        // Em produção (Hostinger), verifica se a URL local é publicamente acessível via HTTP 200
        if (!$isLocal) {
            try {
                $check = Http::withoutVerifying()->timeout(4)->get($localUrl);
                if ($check->successful()) {
                    return $localUrl;
                }
                Log::warning("URL local da Hostinger [{$localUrl}] retornou HTTP {$check->status()}. Ativando fallback de imagem pública.");
            } catch (\Exception $e) {
                Log::warning('URL pública local não acessível na Hostinger, ativando fallback: ' . $e->getMessage());
            }
        }

        // Fallback automático para desenvolvimento local: Upload temporário para Litterbox Catbox (retorna URL direta de imagem compatível com a Meta API)
        $fullPath = storage_path('app/public/' . $cleanPath);
        if (file_exists($fullPath)) {
            try {
                $response = Http::attach(
                    'fileToUpload', file_get_contents($fullPath), basename($fullPath)
                )->post('https://litterbox.catbox.moe/resources/internals/api.php', [
                    'reqtype' => 'fileupload',
                    'time' => '1h'
                ]);

                if ($response->successful() && str_starts_with(trim($response->body()), 'http')) {
                    $directUrl = trim($response->body());
                    Log::info("Localhost fallback: imagem enviada para URL pública temporária: {$directUrl}");
                    return $directUrl;
                }
            } catch (\Exception $e) {
                Log::error('Erro no fallback de imagem para litterbox: ' . $e->getMessage());
            }

            // Fallback secundário: tmpfiles.org
            try {
                $response = Http::attach(
                    'file', file_get_contents($fullPath), basename($fullPath)
                )->post('https://tmpfiles.org/api/v1/upload');

                if ($response->successful() && $response->json('status') === 'success') {
                    $rawUrl = $response->json('data.url');
                    $directUrl = str_replace('tmpfiles.org/', 'tmpfiles.org/dl/', $rawUrl);
                    Log::info("Fallback secundário: imagem enviada para URL pública temporária: {$directUrl}");
                    return $directUrl;
                }
            } catch (\Exception $e) {
                Log::error('Erro no fallback de imagem para tmpfiles: ' . $e->getMessage());
            }
        }

        return $localUrl;
    }

    /**
     * Aguarda o container de mídia ser processado e liberado (status_code === 'FINISHED') pela Meta API.
     */
    private function waitForMetaContainerReady($containerId, $accessToken, $maxSeconds = 20)
    {
        for ($i = 0; $i < $maxSeconds; $i++) {
            try {
                $resp = Http::get("https://graph.facebook.com/v19.0/{$containerId}", [
                    'fields' => 'status_code,status',
                    'access_token' => $accessToken,
                ]);

                if ($resp->successful()) {
                    $code = strtoupper($resp->json('status_code', ''));
                    if ($code === 'FINISHED') {
                        return true;
                    }
                    if ($code === 'ERROR') {
                        $msg = $resp->json('status', 'Erro no processamento da imagem pela Meta.');
                        Log::error("Container Meta {$containerId} falhou com status ERROR: " . $resp->body());
                        throw new \Exception("Erro no processamento da imagem pela Meta: {$msg}");
                    }
                }
            } catch (\Exception $e) {
                if (str_contains($e->getMessage(), 'Erro no processamento')) {
                    throw $e;
                }
                Log::warning("Aguardando verificação do container {$containerId}: " . $e->getMessage());
            }
            sleep(1);
        }
        return true;
    }

    /**
     * Retorna a URI de redirecionamento do OAuth.
     */
    protected function getRedirectUri()
    {
        $uri = config('services.instagram.redirect_uri') ?: env('INSTAGRAM_REDIRECT_URI');
        if ($uri) return $uri;

        $host = request()->getHost();
        if (in_array($host, ['127.0.0.1', 'localhost'])) {
            return 'http://localhost:8000/instagram/callback';
        }

        $baseUrl = route('instagram.callback');

        $isHttps = request()->isSecure()
            || request()->header('x-forwarded-proto') === 'https'
            || request()->header('X-Forwarded-Proto') === 'https'
            || str_starts_with(config('app.url'), 'https://');

        if ($isHttps && str_starts_with($baseUrl, 'http://')) {
            $baseUrl = str_replace('http://', 'https://', $baseUrl);
        }
        return $baseUrl;
    }

    /**
     * Gera uma Legenda Inteligente (Copywriting) com IA ou algoritmo estruturado de Copywriting.
     */
    public function generateAiCaption(Request $request)
    {
        $topic = trim($request->input('topic', ''));
        $tone = $request->input('tone', 'Descontraído');
        $ctaType = $request->input('cta_type', 'salvar');
        $mediaType = $request->input('media_type', 'IMAGE');

        if (empty($topic)) {
            return response()->json([
                'success' => false,
                'message' => 'Por favor, insira um assunto, rascunho ou ideia base para gerar a legenda.'
            ], 422);
        }

        $openAiKey = config('services.openai.api_key') ?: env('OPENAI_API_KEY');
        $geminiKey = config('services.gemini.api_key') ?: env('GEMINI_API_KEY');

        $prompt = "Você é um Copywriter especialista em Mídias Sociais e Instagram.\n";
        $prompt .= "Crie uma legenda altamente engajadora para um post do Instagram do tipo {$mediaType}.\n";
        $prompt .= "Assunto/Tema base: \"{$topic}\"\n";
        $prompt .= "Tom de Voz: \"{$tone}\"\n";
        $prompt .= "Objetivo/CTA Principal: \"{$ctaType}\"\n\n";
        $prompt .= "Diretrizes:\n";
        $prompt .= "1. Use um gancho inicial impactante nas primeiras 2 linhas.\n";
        $prompt .= "2. Divida o texto em parágrafos curtos com espaçamento e emojis apropriados.\n";
        $prompt .= "3. Inclua a Call to Action (CTA) clara no final alinhada ao objetivo escolhido.\n";
        $prompt .= "4. Não coloque hashtags no meio do texto (deixe para o final ou campo separado).\n";
        $prompt .= "5. Retorne APENAS o texto da legenda pronta para publicação.";

        if ($openAiKey) {
            try {
                $response = Http::withoutVerifying()->timeout(6)->withToken($openAiKey)
                    ->post('https://api.openai.com/v1/chat/completions', [
                        'model' => 'gpt-4o-mini',
                        'messages' => [
                            ['role' => 'system', 'content' => 'Você é um assistente especialista em Copywriting para Instagram.'],
                            ['role' => 'user', 'content' => $prompt]
                        ],
                        'temperature' => 0.7,
                    ]);

                if ($response->successful()) {
                    $aiText = trim($response->json('choices.0.message.content', ''));
                    if (!empty($aiText)) {
                        return response()->json([
                            'success' => true,
                            'caption' => $aiText,
                            'provider' => 'OpenAI GPT-4o Mini'
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                Log::warning("OpenAI API call failed: " . $e->getMessage());
            }
        }

        if ($geminiKey) {
            try {
                $response = Http::withoutVerifying()->timeout(6)->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$geminiKey}", [
                    'contents' => [
                        ['parts' => [['text' => $prompt]]]
                    ]
                ]);

                if ($response->successful()) {
                    $aiText = trim($response->json('candidates.0.content.parts.0.text', ''));
                    if (!empty($aiText)) {
                        return response()->json([
                            'success' => true,
                            'caption' => $aiText,
                            'provider' => 'Google Gemini'
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                Log::warning("Gemini API call failed: " . $e->getMessage());
            }
        }

        $generatedCaption = $this->buildFallbackCopywriting($topic, $tone, $ctaType, $mediaType);

        return response()->json([
            'success' => true,
            'caption' => $generatedCaption,
            'provider' => 'Assistente Inteligente Freelas'
        ]);
    }

    /**
     * Motor de Copywriting Estruturado em PHP para fallback local.
     */
    protected function buildFallbackCopywriting(string $topic, string $tone, string $ctaType, string $mediaType): string
    {
        $hooks = [
            'Descontraído' => [
                "Você não vai acreditar nisso... 🤫",
                "Alerta de dica valiosa! Dá uma olhada nisso 👇",
                "Quer melhorar seus resultados sem complicação? Vem comigo!",
            ],
            'Profissional' => [
                "A chave para transformar seus projetos está no detalhe.",
                "Estratégia e execução: o que realmente faz a diferença.",
                "Confira os pontos essenciais que todo profissional precisa saber.",
            ],
            'Persuasivo' => [
                "Se você ignorar isso, pode estar perdendo tempo e dinheiro.",
                "O segredo que os grandes profissionais usam no dia a dia...",
                "Transforme sua forma de trabalhar com este conceito simples.",
            ],
            'Educativo' => [
                "Guia Rápido: Como dominar esse conceito passo a passo 📚",
                "Você já sabia disso? Aprenda a aplicar na prática!",
                "3 lições indispensáveis sobre este assunto 💡",
            ],
            'Minimalista' => [
                "Menos ruído, mais resultado.",
                "Foco no que realmente importa.",
                "Essencial e direto ao ponto.",
            ]
        ];

        $ctas = [
            'salvar' => "📌 Salve este post para consultar sempre que precisar!",
            'comentar' => "💬 Qual é a sua opinião sobre isso? Deixe seu comentário abaixo!",
            'bio' => "🔗 Clique no link da Bio para saber mais e conferir os detalhes completos!",
            'direct' => "📩 Me envie uma mensagem no Direct com a palavra \"INFO\" para conversarmos!",
            'compartilhar' => "🚀 Compartilhe este conteúdo com alguém que precisa ver isso hoje!"
        ];

        $selectedHook = $hooks[$tone][array_rand($hooks[$tone])] ?? "Confira esta dica especial que separamos para você! ✨";
        $selectedCta = $ctas[$ctaType] ?? $ctas['salvar'];

        $formattedTopic = ucfirst($topic);

        $body = "";
        if ($mediaType === 'CAROUSEL') {
            $body = "Arrasta para o lado ➡️ para ver todos os detalhes sobre:\n\n";
            $body .= "✨ {$formattedTopic}\n\n";
            $body .= "1️⃣ Primeiro passo: Defina clareza e objetivo.\n";
            $body .= "2️⃣ Segundo passo: Mantenha a consistência visual e estratégica.\n";
            $body .= "3️⃣ Terceiro passo: Meça os resultados e aprimore sempre.";
        } else {
            $body = "Quando o assunto é {$formattedTopic}, aplicar a estratégia certa faz toda a diferença.\n\n";
            $body .= "Aqui estão os pontos principais para ficar atento:\n";
            $body .= "• Organização e foco na entrega\n";
            $body .= "• Atenção aos detalhes que encantam\n";
            $body .= "• Consistência no dia a dia";
        }

        return "{$selectedHook}\n\n{$body}\n\n{$selectedCta}";
    }

    /**
     * Gera e renderiza a folha de relatório mensal de desempenho para impressão/PDF.
     */
    public function exportPdfReport(Request $request)
    {
        $account = InstagramAccount::where('user_id', auth()->id())->first();
        if (!$account) {
            return redirect()->route('instagram.index')->with('error', 'Nenhuma conta do Instagram conectada.');
        }

        $posts = InstagramPost::where('user_id', auth()->id())->get();

        // Coleta posts do perfil via Meta API
        $liveInstagramPosts = [];
        if ($account->access_token && $account->instagram_account_id) {
            try {
                $url = "https://graph.facebook.com/v19.0/{$account->instagram_account_id}/media?fields=id,caption,media_type,media_url,permalink,timestamp,like_count,comments_count&limit=50&access_token=" . $account->access_token;
                $resp = Http::withoutVerifying()->get($url);
                if ($resp->successful()) {
                    $liveInstagramPosts = $resp->json('data', []);
                }
            } catch (\Throwable $e) {
                Log::error('Erro ao buscar dados para o relatório PDF: ' . $e->getMessage());
            }
        }

        $totalLikes = array_sum(array_column($liveInstagramPosts, 'like_count'));
        $totalComments = array_sum(array_column($liveInstagramPosts, 'comments_count'));
        $totalPostsCount = count($liveInstagramPosts);
        $avgEngagement = $totalPostsCount > 0 ? round(($totalLikes + $totalComments) / $totalPostsCount, 1) : 0;

        return view('instagram.report_pdf', compact('account', 'posts', 'liveInstagramPosts', 'totalLikes', 'totalComments', 'totalPostsCount', 'avgEngagement'));
    }

    /**
     * Responde a um comentário de post diretamente no Instagram via Meta API.
     */
    public function replyComment(Request $request)
    {
        $commentId = $request->input('comment_id');
        $message = trim($request->input('message'));

        if (empty($commentId) || empty($message)) {
            return response()->json(['success' => false, 'message' => 'Comentário e mensagem são obrigatórios.'], 422);
        }

        $account = InstagramAccount::where('user_id', auth()->id())->first();
        if (!$account || !$account->access_token) {
            return response()->json(['success' => false, 'message' => 'Conta do Instagram não conectada.'], 400);
        }

        try {
            $resp = Http::withoutVerifying()->post("https://graph.facebook.com/v19.0/{$commentId}/replies", [
                'message' => $message,
                'access_token' => $account->access_token
            ]);

            if ($resp->successful()) {
                // Notifica no sistema interno que uma resposta foi enviada
                \App\Models\Notification::create([
                    'user_id' => auth()->id(),
                    'title' => '💬 Comentário Respondido no Instagram',
                    'content' => 'Sua resposta "' . \Illuminate\Support\Str::limit($message, 40) . '" foi enviada com sucesso ao seguidor.',
                    'type' => 'instagram_comment_reply'
                ]);

                return response()->json(['success' => true, 'message' => 'Resposta enviada com sucesso!']);
            }

            $metaErr = $resp->json('error.message', 'Erro ao responder');
            if (str_contains($metaErr, 'Missing Permission') || str_contains($metaErr, '#100')) {
                $metaErr = 'Sua conexão com o Instagram precisa da permissão "Gerenciar Comentários". Clique em "Desconectar" e reconecte seu Instagram aceitando todas as permissões do Facebook.';
            }

            return response()->json(['success' => false, 'message' => $metaErr], 400);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Erro de conexão: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Busca os comentários e autores reais de uma publicação específica sob demanda.
     */
    public function getMediaComments($mediaId)
    {
        $account = InstagramAccount::where('user_id', auth()->id())->first();
        if (!$account || !$account->access_token) {
            return response()->json(['success' => false, 'comments' => []]);
        }

        try {
            $resp = Http::withoutVerifying()->get("https://graph.facebook.com/v19.0/{$mediaId}/comments", [
                'fields' => 'id,text,timestamp,username,from{id,username,name},user{id,username,name}',
                'access_token' => $account->access_token
            ]);

            if ($resp->successful()) {
                $comments = $resp->json('data', []);
                foreach ($comments as &$c) {
                    $c['author_name'] = $c['username'] 
                        ?? ($c['from']['username'] 
                        ?? ($c['from']['name'] 
                        ?? ($c['user']['username'] 
                        ?? ($c['user']['name'] 
                        ?? 'seguidor'))));
                    
                    // Garante que username tenha o valor encontrado
                    $c['username'] = $c['author_name'];
                }
                return response()->json(['success' => true, 'comments' => $comments]);
            } else {
                Log::warning("Falha na resposta da Meta ao buscar comentários da mídia {$mediaId}: " . $resp->body());
            }
        } catch (\Throwable $e) {
            Log::error("Erro ao buscar comentários da mídia {$mediaId}: " . $e->getMessage());
        }

        return response()->json(['success' => false, 'comments' => []]);
    }
}
