<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Google\Client as GoogleClient;

class GoogleDriveController extends Controller
{
    /**
     * Inicia o fluxo de autorização OAuth 2.0 com o Google Drive.
     */
    public function connect(Request $request)
    {
        $client = $this->getGoogleClient();
        $authUrl = $client->createAuthUrl();
        return redirect()->away($authUrl);
    }

    /**
     * Recebe o código de autorização do Google e salva o Refresh Token.
     */
    public function callback(Request $request)
    {
        if ($request->has('error')) {
            return redirect()->route('revisoes.shares.index')->with('error', 'Autorização do Google Drive foi cancelada.');
        }

        if (!$request->has('code')) {
            return redirect()->route('revisoes.shares.index')->with('error', 'Código de autorização não recebido do Google.');
        }

        try {
            $client = $this->getGoogleClient();
            $code = $request->get('code');
            $token = $client->fetchAccessTokenWithAuthCode($code);

            if (isset($token['error'])) {
                Log::error('Erro ao obter token do Google API: ', (array) $token);
                $msg = is_array($token['error']) ? json_encode($token['error']) : $token['error'];
                return redirect()->route('revisoes.shares.index')->with('error', 'Erro ao obter token do Google: ' . ($token['error_description'] ?? $msg));
            }

            $refreshToken = $token['refresh_token'] ?? null;
            if (!$refreshToken) {
                // Se não veio refresh token, forçamos re-consentimento
                $client->setPrompt('select_account consent');
                return redirect()->away($client->createAuthUrl());
            }

            // Atualiza runtime config
            config([
                'services.google.refresh_token' => $refreshToken,
                'filesystems.disks.google.refreshToken' => $refreshToken,
            ]);

            // Atualiza o .env com as credenciais obtidas
            $this->updateEnv([
                'GOOGLE_DRIVE_REFRESH_TOKEN' => $refreshToken,
            ]);

            // Tenta criar/obter a pasta raiz "Freelas_Shared_Files" no Google Drive
            $folderId = $this->ensureRootFolderExists($client);
            if ($folderId) {
                config([
                    'services.google.folder_id' => $folderId,
                    'filesystems.disks.google.folder' => $folderId,
                ]);
                $this->updateEnv(['GOOGLE_DRIVE_FOLDER_ID' => $folderId]);
            }

            return redirect()->route('revisoes.shares.index')->with('success', '🎉 Conta do Google Drive conectada com sucesso! O armazenamento de 5 TB já está ativo para seus compartilhamentos.');
        } catch (\Exception $e) {
            Log::error('Erro no callback do Google Drive: ' . $e->getMessage());
            return redirect()->route('revisoes.shares.index')->with('error', 'Falha ao conectar com o Google Drive: ' . $e->getMessage());
        }
    }

    /**
     * Desconecta a integração do Google Drive.
     */
    public function disconnect()
    {
        config([
            'services.google.refresh_token' => '',
            'services.google.folder_id' => '',
            'filesystems.disks.google.refreshToken' => '',
            'filesystems.disks.google.folder' => '',
        ]);

        $this->updateEnv([
            'GOOGLE_DRIVE_REFRESH_TOKEN' => '',
            'GOOGLE_DRIVE_FOLDER_ID' => '',
        ]);

        return redirect()->route('revisoes.shares.index')->with('info', 'Integração do Google Drive desconectada. O armazenamento voltou para o servidor local.');
    }

    /**
     * Retorna a instância configurada do Google Client.
     */
    protected function getGoogleClient()
    {
        $client = new GoogleClient();
        
        $clientId = config('services.google.client_id') ?: env('GOOGLE_DRIVE_CLIENT_ID');
        $clientSecret = config('services.google.client_secret') ?: env('GOOGLE_DRIVE_CLIENT_SECRET');

        $client->setClientId($clientId);
        $client->setClientSecret($clientSecret);
        
        // Permite definir a URI explicitamente em GOOGLE_DRIVE_REDIRECT_URI ou gera dinamicamente respeitando o esquema da requisição
        $redirectUri = config('services.google.redirect_uri') ?: env('GOOGLE_DRIVE_REDIRECT_URI');

        if (!$redirectUri) {
            $isHttps = request()->isSecure() 
                || request()->header('x-forwarded-proto') === 'https'
                || request()->header('X-Forwarded-Proto') === 'https'
                || str_starts_with(config('app.url'), 'https://');

            $baseUrl = url('/google-drive/callback');
            if ($isHttps && str_starts_with($baseUrl, 'http://')) {
                $baseUrl = str_replace('http://', 'https://', $baseUrl);
            }
            $redirectUri = $baseUrl;
        }

        $client->setRedirectUri($redirectUri);

        $client->setScopes([
            'https://www.googleapis.com/auth/drive.file',
            'https://www.googleapis.com/auth/drive'
        ]);
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        return $client;
    }

    /**
     * Garante que existe uma pasta 'Freelas_Shared_Files' no Drive do usuário.
     */
    protected function ensureRootFolderExists(GoogleClient $client)
    {
        try {
            $service = new \Google\Service\Drive($client);
            $query = "mimeType='application/vnd.google-apps.folder' and name='Freelas_Shared_Files' and trashed=false";
            $results = $service->files->listFiles([
                'q' => $query,
                'fields' => 'files(id, name)'
            ]);

            if (count($results->getFiles()) > 0) {
                return $results->getFiles()[0]->getId();
            }

            // Se não existe, cria a pasta
            $folder = new \Google\Service\Drive\DriveFile();
            $folder->setName('Freelas_Shared_Files');
            $folder->setMimeType('application/vnd.google-apps.folder');

            $createdFolder = $service->files->create($folder, ['fields' => 'id']);
            return $createdFolder->getId();
        } catch (\Exception $e) {
            Log::warning('Erro ao criar pasta no Google Drive: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Atualiza valores no arquivo .env de forma segura.
     */
    protected function updateEnv(array $data)
    {
        try {
            $envPath = base_path('.env');
            if (!file_exists($envPath) || !is_writable($envPath)) {
                Log::warning('.env file is missing or not writable.');
                return;
            }

            $content = file_get_contents($envPath);
            foreach ($data as $key => $value) {
                if (preg_match("/^{$key}=.*/m", $content)) {
                    $content = preg_replace("/^{$key}=.*/m", "{$key}=\"{$value}\"", $content);
                } else {
                    $content .= "\n{$key}=\"{$value}\"";
                }
            }
            file_put_contents($envPath, $content);
        } catch (\Throwable $e) {
            Log::warning('Não foi possível escrever no arquivo .env: ' . $e->getMessage());
        }
    }
}
