<?php

namespace App\Http\Controllers;

use App\Models\InstagramAccount;
use App\Models\InstagramPost;
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
    public function index()
    {
        $account = InstagramAccount::where('user_id', auth()->id())->first();
        $posts = InstagramPost::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('instagram.index', compact('account', 'posts'));
    }

    /**
     * Inicia o fluxo de autorização do Facebook / Instagram Graph API.
     */
    public function connect()
    {
        $appId = trim(config('services.instagram.client_id') ?: env('INSTAGRAM_CLIENT_ID', ''));
        $appId = str_replace(['"', "'"], '', $appId);

        if (empty($appId)) {
            return redirect()->route('instagram.index')->with('error', 'O ID do aplicativo (INSTAGRAM_CLIENT_ID) não foi encontrado no arquivo .env ou a configuração está em cache. Execute "php artisan config:clear".');
        }

        $redirectUri = $this->getRedirectUri();

        $scopes = [
            'instagram_basic',
            'instagram_content_publish',
            'pages_show_list',
            'pages_read_engagement',
            'public_profile'
        ];

        $params = http_build_query([
            'client_id' => $appId,
            'redirect_uri' => $redirectUri,
            'scope' => implode(',', $scopes),
            'response_type' => 'code',
            'state' => csrf_token(),
        ]);

        $authUrl = "https://www.facebook.com/v19.0/dialog/oauth?" . $params;

        return redirect()->away($authUrl);
    }

    /**
     * Trata o retorno (callback) da autorização do Facebook / Instagram.
     */
    public function callback(Request $request)
    {
        if ($request->has('error') || !$request->has('code')) {
            $msg = $request->get('error_description', 'Autorização do Instagram/Facebook cancelada ou não concluída.');
            return redirect()->route('instagram.index')->with('error', $msg);
        }

        try {
            $appId = config('services.instagram.client_id') ?: env('INSTAGRAM_CLIENT_ID');
            $appSecret = config('services.instagram.client_secret') ?: env('INSTAGRAM_CLIENT_SECRET');
            $redirectUri = $this->getRedirectUri();
            $code = $request->get('code');

            // 1. Troca o código por um Short-Lived User Access Token
            $response = Http::post('https://graph.facebook.com/v19.0/oauth/access_token', [
                'client_id' => $appId,
                'client_secret' => $appSecret,
                'redirect_uri' => $redirectUri,
                'code' => $code,
            ]);

            if ($response->failed()) {
                Log::error('Erro ao trocar código por token Meta: ' . $response->body());
                return redirect()->route('instagram.index')->with('error', 'Erro ao autenticar com o Facebook: ' . $response->json('error.message', 'Erro desconhecido.'));
            }

            $shortToken = $response->json('access_token');

            // 2. Troca o Short-Lived Token por um Long-Lived Token (válido por ~60 dias)
            $tokenResp = Http::get('https://graph.facebook.com/v19.0/oauth/access_token', [
                'grant_type' => 'fb_exchange_token',
                'client_id' => $appId,
                'client_secret' => $appSecret,
                'fb_exchange_token' => $shortToken,
            ]);

            $longToken = $tokenResp->json('access_token', $shortToken);

            // 3. Obtém as Páginas do Facebook vinculadas para encontrar a conta do Instagram Business
            $pagesResp = Http::get("https://graph.facebook.com/v19.0/me/accounts", [
                'access_token' => $longToken,
                'fields' => 'id,name,instagram_business_account{id,username,name,profile_picture_url}'
            ]);

            if ($pagesResp->failed()) {
                Log::error('Erro ao obter páginas do Facebook: ' . $pagesResp->body());
                return redirect()->route('instagram.index')->with('error', 'Falha ao buscar páginas do Facebook vinculadas.');
            }

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

            if (!$igAccountData) {
                return redirect()->route('instagram.index')->with('error', 'Nenhuma conta profissional do Instagram vinculada à sua Página do Facebook foi encontrada. Certifique-se de que a conta @' . auth()->user()->name . ' está como Profissional e conectada a uma Página.');
            }

            // 4. Salva ou atualiza a conta do Instagram no banco de dados
            InstagramAccount::updateOrCreate(
                [
                    'instagram_account_id' => $igAccountData['id'],
                ],
                [
                    'user_id' => auth()->id(),
                    'facebook_page_id' => $facebookPageId,
                    'username' => $igAccountData['username'] ?? null,
                    'name' => $igAccountData['name'] ?? null,
                    'profile_picture_url' => $igAccountData['profile_picture_url'] ?? null,
                    'access_token' => $longToken,
                    'token_expires_at' => now()->addDays(60),
                    'is_active' => true,
                ]
            );

            return redirect()->route('instagram.index')->with('success', '🎉 Conta do Instagram @' . ($igAccountData['username'] ?? '') . ' conectada com sucesso!');

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
     * Publica ou agendamento de postagem no Instagram.
     */
    public function storePost(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:10240', // Max 10MB
            'caption' => 'nullable|string',
            'action' => 'required|in:now,schedule',
            'scheduled_at' => 'nullable|required_if:action,schedule|date|after:now',
        ]);

        $account = InstagramAccount::where('user_id', auth()->id())->where('is_active', true)->first();

        if (!$account) {
            return redirect()->route('instagram.index')->with('error', 'Conecte sua conta do Instagram antes de publicar.');
        }

        // Salva a imagem no storage público
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
            // Tenta publicar imediatamente
            return $this->publishPostToInstagram($post);
        }

        return redirect()->route('instagram.index')->with('success', '🗓️ Post agendado com sucesso para ' . Carbon::parse($request->scheduled_at)->format('d/m/Y H:i'));
    }

    /**
     * Publica o post no Instagram via Graph API (Container -> Publish).
     */
    public function publishPostToInstagram(InstagramPost $post)
    {
        try {
            $account = $post->instagramAccount;
            if (!$account) {
                throw new \Exception('Conta do Instagram não encontrada.');
            }

            // Gera a URL pública acessível para a Meta baixar a imagem
            $publicImageUrl = asset('storage/' . $post->media_path);

            // 1. Criar o container de mídia no Instagram
            $containerResp = Http::post("https://graph.facebook.com/v19.0/{$account->instagram_account_id}/media", [
                'image_url' => $publicImageUrl,
                'caption' => $post->caption,
                'access_token' => $account->access_token,
            ]);

            if ($containerResp->failed()) {
                $err = $containerResp->json('error.message', 'Erro ao criar mídia no Instagram.');
                Log::error('Erro ao criar container Instagram: ' . $containerResp->body());
                $post->update(['status' => 'erro', 'error_message' => $err]);
                return redirect()->route('instagram.index')->with('error', 'Erro ao enviar para o Instagram: ' . $err);
            }

            $containerId = $containerResp->json('id');

            // 2. Publicar o container de mídia
            $publishResp = Http::post("https://graph.facebook.com/v19.0/{$account->instagram_account_id}/media_publish", [
                'creation_id' => $containerId,
                'access_token' => $account->access_token,
            ]);

            if ($publishResp->failed()) {
                $err = $publishResp->json('error.message', 'Erro ao publicar mídia no Instagram.');
                Log::error('Erro ao publicar mídia Instagram: ' . $publishResp->body());
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
