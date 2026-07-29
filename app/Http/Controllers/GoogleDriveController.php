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

            // Atualiza o .env com as credenciais obtidas
            $this->updateEnv([
                'GOOGLE_DRIVE_REFRESH_TOKEN' => $refreshToken,
            ]);

            // Tenta criar/obter a pasta raiz "Freelas_Shared_Files" no Google Drive
            $folderId = $this->ensureRootFolderExists($client);
            if ($folderId) {
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
        $client->setClientId(env('GOOGLE_DRIVE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_DRIVE_CLIENT_SECRET'));
        
        // Define a URI de redirecionamento correspondente à cadastrada no Google Cloud Console
        $redirectUri = 'http://127.0.0.1:8000/google-drive/callback';
        if (!in_array(request()->getHost(), ['127.0.0.1', 'localhost'])) {
            $redirectUri = url('/google-drive/callback');
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
     * Atualiza valores no arquivo .env
     */
    protected function updateEnv(array $data)
    {
        $envPath = base_path('.env');
        if (!file_exists($envPath)) return;

        $content = file_get_contents($envPath);
        foreach ($data as $key => $value) {
            if (preg_match("/^{$key}=.*/m", $content)) {
                $content = preg_replace("/^{$key}=.*/m", "{$key}=\"{$value}\"", $content);
            } else {
                $content .= "\n{$key}=\"{$value}\"";
            }
        }
        file_put_contents($envPath, $content);
    }
}
