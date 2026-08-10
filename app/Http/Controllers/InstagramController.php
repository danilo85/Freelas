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
     * Inicia o fluxo de autorização do Instagram Business Login (Novo OAuth oficial da Meta).
     */
    public function connect()
    {
        $appId = trim(config('services.instagram.client_id') ?: env('INSTAGRAM_CLIENT_ID', ''));
        $appId = str_replace(['"', "'"], '', $appId);

        if (empty($appId)) {
            return redirect()->route('instagram.index')->with('error', 'O ID do aplicativo (INSTAGRAM_CLIENT_ID) não foi encontrado no arquivo .env ou a configuração está em cache. Execute "php artisan config:clear".');
        }

        $redirectUri = $this->getRedirectUri();

        // Permissões atuais do Instagram Business Login
        $scopes = [
            'instagram_business_basic',
            'instagram_business_content_publish',
        ];

        $params = http_build_query([
            'client_id' => $appId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => implode(',', $scopes),
            'state' => csrf_token(),
        ]);

        // URL oficial de OAuth direto do Instagram
        $authUrl = "https://www.instagram.com/oauth/authorize?" . $params;

        return redirect()->away($authUrl);
    }

    /**
     * Trata o retorno (callback) da autorização do Instagram.
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

            // 1. Troca o código de autorização por um Short-Lived Access Token direto da API do Instagram
            $response = Http::asForm()->post('https://api.instagram.com/oauth/access_token', [
                'client_id' => $appId,
                'client_secret' => $appSecret,
                'grant_type' => 'authorization_code',
                'redirect_uri' => $redirectUri,
                'code' => $code,
            ]);

            if ($response->failed()) {
                Log::error('Erro ao trocar código por token Instagram: ' . $response->body());
                $err = $response->json('error_message', $response->json('error.message', 'Erro ao obter token de acesso do Instagram.'));
                return redirect()->route('instagram.index')->with('error', $err);
            }

            $shortToken = $response->json('access_token');
            $userIgId = $response->json('user_id');

            // 2. Troca o Short-Lived Token por um Long-Lived Token (válido por ~60 dias)
            $tokenResp = Http::get('https://graph.instagram.com/access_token', [
                'grant_type' => 'ig_exchange_token',
                'client_secret' => $appSecret,
                'access_token' => $shortToken,
            ]);

            $longToken = $tokenResp->json('access_token', $shortToken);

            // 3. Consulta as informações do Perfil no Graph Instagram API
            $profileResp = Http::get("https://graph.instagram.com/v19.0/me", [
                'fields' => 'id,username,name,profile_picture_url',
                'access_token' => $longToken,
            ]);

            $profileData = $profileResp->json();
            $igAccountId = $profileData['id'] ?? $userIgId;
            $username = $profileData['username'] ?? 'danilomigueldesigner';
            $name = $profileData['name'] ?? $username;
            $profilePic = $profileData['profile_picture_url'] ?? null;

            // 4. Salva ou atualiza a conta conectada no banco do Laravel
            InstagramAccount::updateOrCreate(
                [
                    'instagram_account_id' => $igAccountId,
                ],
                [
                    'user_id' => auth()->id(),
                    'username' => $username,
                    'name' => $name,
                    'profile_picture_url' => $profilePic,
                    'access_token' => $longToken,
                    'token_expires_at' => now()->addDays(60),
                    'is_active' => true,
                ]
            );

            return redirect()->route('instagram.index')->with('success', '🎉 Conta do Instagram @' . $username . ' conectada com sucesso!');

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
            $containerResp = Http::post("https://graph.instagram.com/v19.0/{$account->instagram_account_id}/media", [
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

            // 2. Publicar container
            $publishResp = Http::post("https://graph.instagram.com/v19.0/{$account->instagram_account_id}/media_publish", [
                'creation_id' => $containerId,
                'access_token' => $account->access_token,
            ]);

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
