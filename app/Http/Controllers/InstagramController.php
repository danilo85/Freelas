<?php

namespace App\Http\Controllers;

use App\Models\InstagramAccount;
use App\Models\InstagramPost;
use App\Models\InstagramSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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

            // Busca o Feed real de posts já publicados no perfil do Instagram (sem limite baixo de páginas)
            $liveInstagramPosts = [];
            if ($account && $account->access_token && $account->instagram_account_id) {
                try {
                    $nextUrl = "https://graph.facebook.com/v19.0/{$account->instagram_account_id}/media?fields=id,caption,media_type,media_url,permalink,thumbnail_url,timestamp,like_count,comments_count,children{id,media_url,thumbnail_url,media_type}&limit=50&access_token=" . $account->access_token;
                    
                    $maxPages = 30; // Permite buscar até 1500 publicações (traz todos os posts do perfil)
                    $pageCount = 0;

                    while ($nextUrl && $pageCount < $maxPages) {
                        $feedResp = Http::withoutVerifying()->timeout(15)->get($nextUrl);
                        if ($feedResp->failed()) break;

                        $data = $feedResp->json('data', []);
                        if (empty($data) || !is_array($data)) break;

                        $liveInstagramPosts = array_merge($liveInstagramPosts, $data);

                        $nextUrl = $feedResp->json('paging.next');
                        $pageCount++;
                    }

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
                } catch (\Throwable $e) {
                    Log::error('Erro ao buscar feed vivo do Instagram: ' . $e->getMessage());
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
            'logo_icon' => 'nullable|image|max:5120',
            'arrow_icon' => 'nullable|image|max:5120',
        ]);

        $settings = InstagramSetting::firstOrCreate(['user_id' => auth()->id()]);

        if ($request->hasFile('logo_icon')) {
            if ($settings->logo_path) {
                Storage::disk('public')->delete($settings->logo_path);
            }
            $settings->logo_path = $request->file('logo_icon')->store('instagram_overlays', 'public');
        }

        if ($request->hasFile('arrow_icon')) {
            if ($settings->arrow_path) {
                Storage::disk('public')->delete($settings->arrow_path);
            }
            $settings->arrow_path = $request->file('arrow_icon')->store('instagram_overlays', 'public');
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
                'pages_show_list',
                'pages_read_engagement',
                'pages_manage_posts',
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
        $request->validate([
            'instagram_account_id' => 'nullable|exists:instagram_accounts,id',
            'media_type' => 'required|in:IMAGE,CAROUSEL,STORY',
            'image' => 'nullable|required_if:media_type,IMAGE,STORY|image|max:10240',
            'carousel_images.*' => 'nullable|image|max:10240',
            'caption' => 'nullable|string',
            'has_logo_overlay' => 'nullable|boolean',
            'has_arrow_overlay' => 'nullable|boolean',
            'action' => 'required|in:now,schedule',
            'scheduled_at' => 'nullable|required_if:action,schedule|date|after:now',
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

        $mainPath = null;
        $mediaUrls = [];

        if ($mediaType === 'CAROUSEL') {
            if (!$request->hasFile('carousel_images') || count($request->file('carousel_images')) < 2) {
                return redirect()->back()->with('error', 'Selecione pelo menos 2 imagens para criar um Carrossel.');
            }

            foreach ($request->file('carousel_images') as $imgFile) {
                $rawPath = $imgFile->store('instagram_posts', 'public');
                $processedPath = $this->applyOverlays($rawPath, $hasLogo, $hasArrow);
                $mediaUrls[] = $processedPath;
            }
            $mainPath = $mediaUrls[0] ?? null;
        } else {
            if ($request->hasFile('image')) {
                $rawPath = $request->file('image')->store('instagram_posts', 'public');
                $mainPath = $this->applyOverlays($rawPath, $hasLogo, $hasArrow);
            }
        }

        $post = InstagramPost::create([
            'user_id' => auth()->id(),
            'instagram_account_id' => $account->id,
            'media_type' => $mediaType,
            'media_path' => $mainPath,
            'media_urls' => $mediaUrls,
            'caption' => $request->caption,
            'has_logo_overlay' => $hasLogo,
            'has_arrow_overlay' => $hasArrow,
            'status' => $request->action === 'now' ? 'rascunho' : 'agendado',
            'scheduled_at' => $request->action === 'schedule' ? Carbon::parse($request->scheduled_at) : null,
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

        return redirect()->route('instagram.index')->with('success', '🗓️ Conteúdo agendado com sucesso para ' . Carbon::parse($request->scheduled_at)->format('d/m/Y H:i'));
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

                $itemContainerIds[] = $itemResp->json('id');
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

        $settings = InstagramSetting::where('user_id', auth()->id())->first();

        // 1. Logo Overlay (Topo Direita)
        if ($hasLogo && $settings && $settings->logo_path && file_exists(storage_path('app/public/' . $settings->logo_path))) {
            $logoFullPath = storage_path('app/public/' . $settings->logo_path);
            $logoInfo = @getimagesize($logoFullPath);
            if ($logoInfo) {
                $logoSrc = @imagecreatefrompng($logoFullPath) ?: @imagecreatefromjpeg($logoFullPath);
                if ($logoSrc) {
                    imagealphablending($logoSrc, true);
                    imagesavealpha($logoSrc, true);
                    $logoW = imagesx($logoSrc);
                    $logoH = imagesy($logoSrc);

                    $targetW = (int)($width * 0.18); // 18% da largura da imagem
                    $targetH = (int)($logoH * ($targetW / max(1, $logoW)));
                    $posX = $width - $targetW - (int)($width * 0.04);
                    $posY = (int)($height * 0.04);

                    imagecopyresampled($srcImg, $logoSrc, $posX, $posY, 0, 0, $targetW, $targetH, $logoW, $logoH);
                    imagedestroy($logoSrc);
                }
            }
        }

        // 2. Arrow Overlay (Rodapé Direita - Arraste pro lado / Seta)
        if ($hasArrow && $settings && $settings->arrow_path && file_exists(storage_path('app/public/' . $settings->arrow_path))) {
            $arrowFullPath = storage_path('app/public/' . $settings->arrow_path);
            $arrowInfo = @getimagesize($arrowFullPath);
            if ($arrowInfo) {
                $arrowSrc = @imagecreatefrompng($arrowFullPath) ?: @imagecreatefromjpeg($arrowFullPath);
                if ($arrowSrc) {
                    imagealphablending($arrowSrc, true);
                    imagesavealpha($arrowSrc, true);
                    $arrowW = imagesx($arrowSrc);
                    $arrowH = imagesy($arrowSrc);

                    $targetW = (int)($width * 0.14); // 14% da largura da imagem
                    $targetH = (int)($arrowH * ($targetW / max(1, $arrowW)));
                    $posX = $width - $targetW - (int)($width * 0.05);
                    $posY = $height - $targetH - (int)($height * 0.05);

                    imagecopyresampled($srcImg, $arrowSrc, $posX, $posY, 0, 0, $targetW, $targetH, $arrowW, $arrowH);
                    imagedestroy($arrowSrc);
                }
            }
        }

        // Salva imagem modificada
        $newFilename = 'instagram_posts/overlay_' . time() . '_' . uniqid() . '.jpg';
        $savePath = storage_path('app/public/' . $newFilename);
        imagejpeg($srcImg, $savePath, 90);
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
     * Exclui um tema de hashtags personalizado.
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
        $localUrl = asset('storage/' . $relativePath);

        // Se houver PUBLIC_URL configurado no .env (ex: Ngrok), utiliza ela
        $publicBase = env('PUBLIC_URL');
        if ($publicBase && !str_contains($publicBase, 'localhost') && !str_contains($publicBase, '127.0.0.1') && !str_contains($publicBase, '.test')) {
            return rtrim($publicBase, '/') . '/storage/' . ltrim($relativePath, '/');
        }

        // Se for URL pública de produção (sem localhost/127.0.0.1/.test), usa a própria URL
        $host = parse_url($localUrl, PHP_URL_HOST) ?? '';
        if (!str_contains($host, 'localhost') && !str_contains($host, '127.0.0.1') && !str_contains($host, '.test')) {
            return $localUrl;
        }

        // Fallback automático para desenvolvimento local: Upload temporário para catbox
        $fullPath = storage_path('app/public/' . $relativePath);
        if (file_exists($fullPath)) {
            try {
                $response = Http::asMultipart()->post('https://litterbox.catbox.moe/resources/internals/api.php', [
                    'reqtype' => 'fileupload',
                    'time' => '1h',
                    'fileToUpload' => fopen($fullPath, 'r'),
                ]);

                if ($response->successful() && str_starts_with(trim($response->body()), 'http')) {
                    $publicUrl = trim($response->body());
                    Log::info("Localhost fallback: imagem enviada para URL pública temporária: {$publicUrl}");
                    return $publicUrl;
                }
            } catch (\Exception $e) {
                Log::error('Erro no fallback local de imagem para catbox: ' . $e->getMessage());
            }
        }

        return $localUrl;
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
}
