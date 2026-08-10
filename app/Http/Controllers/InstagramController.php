<?php

namespace App\Http\Controllers;

use App\Models\InstagramAccount;
use App\Models\InstagramPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class InstagramController extends Controller
{
    /**
     * Exibe o painel de gerenciamento do Instagram.
     */
    public function index()
    {
        $account = InstagramAccount::where('user_id', auth()->id())->first();
        $posts = InstagramPost::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('instagram.index', compact('account', 'posts'));
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
            ];
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

            // 1. Tenta trocar o código via Meta Graph API (Facebook Login)
            $response = Http::post('https://graph.facebook.com/v19.0/oauth/access_token', [
                'client_id' => $appId,
                'client_secret' => $appSecret,
                'redirect_uri' => $redirectUri,
                'code' => $code,
            ]);

            if ($response->successful()) {
                $shortToken = $response->json('access_token');

                // Troca por Long-Lived Token (60 dias)
                $tokenResp = Http::get('https://graph.facebook.com/v19.0/oauth/access_token', [
                    'grant_type' => 'fb_exchange_token',
                    'client_id' => $appId,
                    'client_secret' => $appSecret,
                    'fb_exchange_token' => $shortToken,
                ]);

                $longToken = $tokenResp->json('access_token', $shortToken);

                // Busca as páginas do Facebook e conta do Instagram vinculada
                $pagesResp = Http::get("https://graph.facebook.com/v19.0/me/accounts", [
                    'access_token' => $longToken,
                    'fields' => 'id,name,instagram_business_account{id,username,name,profile_picture_url}'
                ]);

                $pages = $pagesResp->json('data', []);
                $igAccountData = null;
                $facebookPageId = null;

                foreach ($pages as $page) {
                    if (isset($page['instagram_business_account'])) {
                        $igAccountData = $page['instagram_business_account'];
                        $facebookPageId = $page['id'];
                        break;
                    }
                }

                if ($igAccountData) {
                    InstagramAccount::updateOrCreate(
                        ['instagram_account_id' => $igAccountData['id']],
                        [
                            'user_id' => auth()->id(),
                            'facebook_page_id' => $facebookPageId,
                            'username' => $igAccountData['username'] ?? 'instagram_user',
                            'name' => $igAccountData['name'] ?? null,
                            'profile_picture_url' => $igAccountData['profile_picture_url'] ?? null,
                            'access_token' => $longToken,
                            'token_expires_at' => now()->addDays(60),
                            'is_active' => true,
                        ]
                    );

                    return redirect()->route('instagram.index')->with('success', '🎉 Conta do Instagram @' . ($igAccountData['username'] ?? '') . ' conectada com sucesso!');
                }
            }

            // 2. Se falhar, tenta o fluxo direto do Instagram API (Instagram Business Login)
            $responseDirect = Http::asForm()->post('https://api.instagram.com/oauth/access_token', [
                'client_id' => $appId,
                'client_secret' => $appSecret,
                'grant_type' => 'authorization_code',
                'redirect_uri' => $redirectUri,
                'code' => $code,
            ]);

            if ($responseDirect->successful()) {
                $shortToken = $responseDirect->json('access_token');
                $userIgId = $responseDirect->json('user_id');

                $tokenResp = Http::get('https://graph.instagram.com/access_token', [
                    'grant_type' => 'ig_exchange_token',
                    'client_secret' => $appSecret,
                    'access_token' => $shortToken,
                ]);

                $longToken = $tokenResp->json('access_token', $shortToken);

                $profileResp = Http::get("https://graph.instagram.com/v19.0/me", [
                    'fields' => 'id,username,name,profile_picture_url',
                    'access_token' => $longToken,
                ]);

                $profileData = $profileResp->json();
                $igAccountId = $profileData['id'] ?? $userIgId;
                $username = $profileData['username'] ?? 'danilomigueldesigner';

                InstagramAccount::updateOrCreate(
                    ['instagram_account_id' => $igAccountId],
                    [
                        'user_id' => auth()->id(),
                        'username' => $username,
                        'name' => $profileData['name'] ?? $username,
                        'profile_picture_url' => $profileData['profile_picture_url'] ?? null,
                        'access_token' => $longToken,
                        'token_expires_at' => now()->addDays(60),
                        'is_active' => true,
                    ]
                );

                return redirect()->route('instagram.index')->with('success', '🎉 Conta do Instagram @' . $username . ' conectada com sucesso!');
            }

            Log::error('Erro ao trocar código por token Meta/Instagram: ' . $response->body() . ' | ' . $responseDirect->body());
            return redirect()->route('instagram.index')->with('error', 'Erro ao obter token do Instagram: ' . ($response->json('error.message') ?? $responseDirect->json('error_message', 'Falha na autenticação.')));

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
     * Cadastra ou agenda a postagem.
     */
    public function storePost(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:10240',
            'caption' => 'nullable|string',
            'action' => 'required|in:now,schedule',
            'scheduled_at' => 'nullable|required_if:action,schedule|date|after:now',
        ]);

        $account = InstagramAccount::where('user_id', auth()->id())->where('is_active', true)->first();

        if (!$account) {
            return redirect()->route('instagram.index')->with('error', 'Conecte sua conta do Instagram antes de publicar.');
        }

        $path = $request->file('image')->store('instagram_posts', 'public');

        $post = InstagramPost::create([
            'user_id' => auth()->id(),
            'instagram_account_id' => $account->id,
            'media_type' => 'IMAGE',
            'media_path' => $path,
            'caption' => $request->caption,
            'status' => $request->action === 'now' ? 'rascunho' : 'agendado',
            'scheduled_at' => $request->action === 'schedule' ? Carbon::parse($request->scheduled_at) : null,
        ]);

        if ($request->action === 'now') {
            return $this->publishPostToInstagram($post);
        }

        return redirect()->route('instagram.index')->with('success', '🗓️ Post agendado com sucesso para ' . Carbon::parse($request->scheduled_at)->format('d/m/Y H:i'));
    }

    /**
     * Publica a imagem diretamente na conta conectada via Instagram Graph API.
     */
    public function publishPostToInstagram(InstagramPost $post)
    {
        try {
            $account = $post->instagramAccount;
            if (!$account) {
                throw new \Exception('Conta do Instagram não encontrada.');
            }

            $publicImageUrl = asset('storage/' . $post->media_path);

            // 1. Criar container de mídia na API do Instagram
            $containerResp = Http::post("https://graph.facebook.com/v19.0/{$account->instagram_account_id}/media", [
                'image_url' => $publicImageUrl,
                'caption' => $post->caption,
                'access_token' => $account->access_token,
            ]);

            if ($containerResp->failed()) {
                // Tenta via Graph Instagram API
                $containerResp = Http::post("https://graph.instagram.com/v19.0/{$account->instagram_account_id}/media", [
                    'image_url' => $publicImageUrl,
                    'caption' => $post->caption,
                    'access_token' => $account->access_token,
                ]);
            }

            if ($containerResp->failed()) {
                $err = $containerResp->json('error.message', 'Erro ao enviar imagem ao Instagram.');
                Log::error('Erro container Instagram: ' . $containerResp->body());
                $post->update(['status' => 'erro', 'error_message' => $err]);
                return redirect()->route('instagram.index')->with('error', 'Erro ao enviar para o Instagram: ' . $err);
            }

            $containerId = $containerResp->json('id');

            // 2. Publicar container
            $publishResp = Http::post("https://graph.facebook.com/v19.0/{$account->instagram_account_id}/media_publish", [
                'creation_id' => $containerId,
                'access_token' => $account->access_token,
            ]);

            if ($publishResp->failed()) {
                $publishResp = Http::post("https://graph.instagram.com/v19.0/{$account->instagram_account_id}/media_publish", [
                    'creation_id' => $containerId,
                    'access_token' => $account->access_token,
                ]);
            }

            if ($publishResp->failed()) {
                $err = $publishResp->json('error.message', 'Erro ao publicar no Instagram.');
                Log::error('Erro publicar Instagram: ' . $publishResp->body());
                $post->update(['status' => 'erro', 'error_message' => $err]);
                return redirect()->route('instagram.index')->with('error', 'Erro ao publicar no Instagram: ' . $err);
            }

            $mediaId = $publishResp->json('id');

            $post->update([
                'status' => 'publicado',
                'published_at' => now(),
                'instagram_media_id' => $mediaId,
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

        $isHttps = request()->isSecure()
            || request()->header('x-forwarded-proto') === 'https'
            || request()->header('X-Forwarded-Proto') === 'https'
            || str_starts_with(config('app.url'), 'https://');

        $baseUrl = url('/instagram/callback');
        if ($isHttps && str_starts_with($baseUrl, 'http://')) {
            $baseUrl = str_replace('http://', 'https://', $baseUrl);
        }
        return $baseUrl;
    }
}
