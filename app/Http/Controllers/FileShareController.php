<?php

namespace App\Http\Controllers;

use App\Models\FileShare;
use App\Models\FileShareItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class FileShareController extends Controller
{
    /**
     * Exibe o painel de gerenciamento de compartilhamentos.
     */
    public function index(Request $request)
    {
        $userId = auth()->id();
        
        // Carrega compartilhamentos ordenando pelos mais recentes
        $query = FileShare::where('user_id', $userId)->with('items');

        // Filtro de busca por título ou descrição
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filtro de status
        if ($request->filled('status')) {
            $status = $request->get('status');
            if ($status === 'ativo') {
                $query->where('is_active', true)->where('expires_at', '>', now());
            } elseif ($status === 'inativo') {
                $query->where('is_active', false);
            } elseif ($status === 'expirado') {
                $query->where('expires_at', '<=', now());
            }
        }

        // Relevante: não filtramos is_hidden no banco de dados para permitir o toggle instantâneo client-side via Alpine.js

        $shares = $query->orderBy('created_at', 'desc')->get();

        // Cálculo de estatísticas importantes
        $totalStorage = 0;
        $totalDownloads = 0;
        $activeSharesCount = 0;
        $expiredSharesCount = 0;

        foreach ($shares as $share) {
            $totalStorage += $share->items->sum('file_size');
            $totalDownloads += $share->download_count;
            if ($share->is_active && $share->expires_at->isFuture()) {
                $activeSharesCount++;
            }
            if ($share->expires_at->isPast()) {
                $expiredSharesCount++;
            }
        }

        $isGoogleConnected = !empty(config('services.google.refresh_token')) || !empty(env('GOOGLE_DRIVE_REFRESH_TOKEN'));

        return view('shares.index', compact(
            'shares',
            'totalStorage',
            'totalDownloads',
            'activeSharesCount',
            'expiredSharesCount',
            'isGoogleConnected'
        ));
    }

    /**
     * Tela de upload e configurações do novo compartilhamento.
     */
    public function create()
    {
        return view('shares.create');
    }

    /**
     * Salva o novo compartilhamento e seus arquivos.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'expires_days' => 'required|integer|min:1|max:30',
            'download_limit' => 'nullable|integer|min:1',
            'password' => 'nullable|string|max:50',
            'files' => 'required|array|min:1',
            'files.*' => 'required|file',
        ]);

        // Validação de limite de tamanho total de 1GB
        $totalSize = 0;
        foreach ($request->file('files') as $file) {
            $totalSize += $file->getSize();
        }

        if ($totalSize > 1024 * 1024 * 1024) { // 1GB em bytes
            return back()->withErrors(['files' => 'O tamanho total dos arquivos enviados excede o limite de 1GB.'])->withInput();
        }

        // Define título inteligente se vazio
        $title = $request->title;
        if (empty($title)) {
            $firstFile = $request->file('files')[0];
            $count = count($request->file('files'));
            if ($count === 1) {
                $title = pathinfo($firstFile->getClientOriginalName(), PATHINFO_FILENAME);
            } else {
                $title = 'Transferência com ' . $count . ' arquivos';
            }
        }

        // Cria o compartilhamento
        // Salva os arquivos e anexa ao compartilhamento (respeita a escolha do usuário ou fallback)
        $userChoice = $request->get('storage_disk', 'google');
        $hasGoogle = !empty(config('services.google.refresh_token')) || !empty(env('GOOGLE_DRIVE_REFRESH_TOKEN'));
        $disk = ($userChoice === 'google' && $hasGoogle) ? 'google' : 'public';

        $share = FileShare::create([
            'user_id' => auth()->id(),
            'share_token' => Str::random(32),
            'title' => $title,
            'description' => $request->description,
            'expires_at' => Carbon::now()->addDays((int) $request->expires_days)->endOfDay(),
            'download_limit' => $request->download_limit,
            'password' => ($request->has('has_password') && $request->filled('password')) ? bcrypt($request->password) : null,
            'is_active' => true,
            'storage_disk' => $disk,
        ]);

        foreach ($request->file('files') as $file) {
            $path = $file->store('shares', $disk);
            $share->items()->create([
                'filename' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getClientMimeType(),
            ]);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Arquivos compartilhados e prontos para envio!',
                'redirect_url' => route('revisoes.shares.index')
            ]);
        }

        return redirect()->route('revisoes.shares.index')
            ->with('success', 'Arquivos compartilhados e prontos para envio!');
    }

    /**
     * Alterna o status ativo do link de compartilhamento.
     */
    public function toggleActive(FileShare $share)
    {
        abort_if($share->user_id !== auth()->id(), 403);
        $share->update(['is_active' => !$share->is_active]);

        return back()->with('success', 'Status do compartilhamento atualizado.');
    }

    /**
     * Atualiza as configurações inline de validade, limite e senha.
     */
    public function updateSettings(Request $request, FileShare $share)
    {
        abort_if($share->user_id !== auth()->id(), 403);

        $request->validate([
            'expires_days' => 'required|integer|min:1|max:30',
            'download_limit' => 'nullable|integer|min:1',
            'password' => 'nullable|string|max:50',
        ]);

        $updateData = [
            'expires_at' => Carbon::parse($share->created_at)->addDays((int) $request->expires_days)->endOfDay(),
            'download_limit' => $request->download_limit,
        ];

        if (!$request->has('has_password')) {
            $updateData['password'] = null;
        } elseif ($request->filled('password')) {
            $updateData['password'] = bcrypt($request->password);
        }

        $share->update($updateData);

        return back()->with('success', 'Configurações de compartilhamento atualizadas.');
    }

    /**
     * Remove o compartilhamento e exclui fisicamente todos os arquivos do disco.
     */
    public function destroy(FileShare $share)
    {
        abort_if($share->user_id !== auth()->id(), 403);

        foreach ($share->items as $item) {
            try {
                if (!empty(env('GOOGLE_DRIVE_REFRESH_TOKEN')) && Storage::disk('google')->exists($item->file_path)) {
                    Storage::disk('google')->delete($item->file_path);
                }
            } catch (\Throwable $e) {}

            try {
                if (Storage::disk('public')->exists($item->file_path)) {
                    Storage::disk('public')->delete($item->file_path);
                }
            } catch (\Throwable $e) {}
        }

        $share->delete();

        return back()->with('success', 'Compartilhamento excluído com sucesso.');
    }

    /**
     * Remove todos os compartilhamentos expirados do usuário e exclui os arquivos do servidor.
     */
    public function destroyExpired()
    {
        $userId = auth()->id();
        $expiredShares = FileShare::where('user_id', $userId)
            ->where('expires_at', '<=', now())
            ->with('items')
            ->get();

        if ($expiredShares->isEmpty()) {
            return back()->with('info', 'Nenhum compartilhamento expirado encontrado.');
        }

        $count = 0;
        foreach ($expiredShares as $share) {
            foreach ($share->items as $item) {
                try {
                    if (!empty(env('GOOGLE_DRIVE_REFRESH_TOKEN')) && Storage::disk('google')->exists($item->file_path)) {
                        Storage::disk('google')->delete($item->file_path);
                    }
                } catch (\Throwable $e) {}

                try {
                    if (Storage::disk('public')->exists($item->file_path)) {
                        Storage::disk('public')->delete($item->file_path);
                    }
                } catch (\Throwable $e) {}
            }
            $share->delete();
            $count++;
        }

        return back()->with('success', "{$count} compartilhamento(s) expirado(s) e seus arquivos foram excluídos com sucesso.");
    }

    /**
     * Oculta ou exibe um compartilhamento.
     */
    public function toggleVisibility(FileShare $share)
    {
        abort_if($share->user_id !== auth()->id(), 403);

        $share->update([
            'is_hidden' => !$share->is_hidden
        ]);

        $msg = $share->is_hidden ? 'Compartilhamento ocultado com sucesso.' : 'Compartilhamento exibido com sucesso.';
        return back()->with('success', $msg);
    }

    /**
     * Visualização pública dos arquivos compartilhados (com suporte a senha).
     */
    public function publicShow(string $shareToken)
    {
        $share = FileShare::where('share_token', $shareToken)
            ->with('items')
            ->firstOrFail();

        // Incrementa o contador de visualizações uma vez por sessão
        $sessionKey = "share_viewed_{$share->id}";
        if (!session()->has($sessionKey)) {
            $share->increment('view_count');
            session()->put($sessionKey, true);
        }

        // Verifica expiração e status ativo
        $isExpired = $share->expires_at->isPast();
        $isInactive = !$share->is_active;

        if ($isExpired || $isInactive) {
            return view('shares.public_show', compact('share', 'isExpired', 'isInactive'));
        }

        // Se tem senha e não autenticado na sessão
        $requiresPassword = $share->password && session("share_auth_{$share->id}") !== true;

        return view('shares.public_show', compact('share', 'isExpired', 'isInactive', 'requiresPassword'));
    }

    /**
     * Verifica a senha do link compartilhado.
     */
    public function publicVerifyPassword(Request $request, string $shareToken)
    {
        $share = FileShare::where('share_token', $shareToken)->firstOrFail();
        
        $request->validate([
            'password' => 'required|string',
        ]);

        if (Hash::check($request->password, $share->password)) {
            session()->put("share_auth_{$share->id}", true);
            return redirect()->back();
        }

        return redirect()->back()->withErrors(['password' => 'Senha incorreta. Tente novamente.']);
    }

    /**
     * Faz download individual de um arquivo do compartilhamento.
     */
    public function publicDownloadFile(string $shareToken, int $itemId)
    {
        $share = FileShare::where('share_token', $shareToken)
            ->where('is_active', true)
            ->where('expires_at', '>', now())
            ->firstOrFail();

        // Verifica senha
        if ($share->password && session("share_auth_{$share->id}") !== true) {
            abort(403, 'Acesso não autorizado.');
        }

        // Verifica limite de downloads
        if ($share->download_limit && $share->download_count >= $share->download_limit) {
            abort(403, 'Limite de downloads atingido.');
        }

        $item = $share->items()->findOrFail($itemId);

        $disk = 'public';
        try {
            if (!empty(env('GOOGLE_DRIVE_REFRESH_TOKEN')) && Storage::disk('google')->exists($item->file_path)) {
                $disk = 'google';
            }
        } catch (\Throwable $e) {
            $disk = 'public';
        }

        // Evita múltiplas notificações e incrementos em downloads multi-thread no intervalo de 15 segundos
        $recent = \App\Models\Notification::where('user_id', $share->user_id)
            ->where('type', 'share')
            ->where('title', 'Arquivo Baixado')
            ->where('content', "O arquivo '" . $item->filename . "' foi baixado do compartilhamento '" . $share->title . "'.")
            ->where('created_at', '>=', now()->subSeconds(15))
            ->exists();

        if (!$recent) {
            $share->increment('download_count');

            // Cria notificação de download para o gestor
            \App\Models\Notification::create([
                'user_id' => $share->user_id,
                'title' => 'Arquivo Baixado',
                'content' => "O arquivo '" . $item->filename . "' foi baixado do compartilhamento '" . $share->title . "'.",
                'type' => 'share'
            ]);
        }

        return Storage::disk($disk)->download($item->file_path, $item->filename);
    }

    /**
     * Empacota todos os arquivos de um compartilhamento em ZIP e inicia o download.
     */
    public function publicDownloadZip(string $shareToken)
    {
        $share = FileShare::where('share_token', $shareToken)
            ->where('is_active', true)
            ->where('expires_at', '>', now())
            ->firstOrFail();

        // Verifica senha
        if ($share->password && session("share_auth_{$share->id}") !== true) {
            abort(403, 'Acesso não autorizado.');
        }

        // Verifica limite de downloads
        if ($share->download_limit && $share->download_count >= $share->download_limit) {
            abort(403, 'Limite de downloads atingido.');
        }

        $tempDir = sys_get_temp_dir();
        $zipFilePath = $tempDir . DIRECTORY_SEPARATOR . 'share_' . Str::random(12) . '.zip';
        $zip = new \ZipArchive();

        if ($zip->open($zipFilePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            $addedCount = 0;
            foreach ($share->items as $item) {
                $content = null;
                try {
                    if (!empty(env('GOOGLE_DRIVE_REFRESH_TOKEN')) && Storage::disk('google')->exists($item->file_path)) {
                        $content = Storage::disk('google')->get($item->file_path);
                    }
                } catch (\Throwable $e) {}

                if (!$content && Storage::disk('public')->exists($item->file_path)) {
                    $content = Storage::disk('public')->get($item->file_path);
                }

                if ($content) {
                    $zip->addFromString($item->filename, $content);
                    $addedCount++;
                }
            }
            $zip->close();
        }

        if (!file_exists($zipFilePath) || filesize($zipFilePath) === 0) {
            return back()->with('error', 'Não foi possível gerar o arquivo ZIP dos itens compartilhados.');
        }

        // Evita múltiplas notificações e incrementos no intervalo de 15 segundos
        $recentZip = \App\Models\Notification::where('user_id', $share->user_id)
            ->where('type', 'share')
            ->where('title', 'Arquivos Baixados (ZIP)')
            ->where('content', "Todos os arquivos do compartilhamento '" . $share->title . "' foram baixados em formato ZIP.")
            ->where('created_at', '>=', now()->subSeconds(15))
            ->exists();

        if (!$recentZip) {
            $share->increment('download_count');

            // Cria notificação de download do ZIP para o gestor
            \App\Models\Notification::create([
                'user_id' => $share->user_id,
                'title' => 'Arquivos Baixados (ZIP)',
                'content' => "Todos os arquivos do compartilhamento '" . $share->title . "' foram baixados em formato ZIP.",
                'type' => 'share'
            ]);
        }

        $downloadName = Str::slug($share->title) . '-arquivos.zip';
        return response()->download($zipFilePath, $downloadName)->deleteFileAfterSend(true);
    }

    public function formatBytes($bytes, $precision = 2)
    {
        if ($bytes <= 0) return '0 Bytes';
        $units = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
