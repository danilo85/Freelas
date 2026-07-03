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

        return view('shares.index', compact(
            'shares',
            'totalStorage',
            'totalDownloads',
            'activeSharesCount',
            'expiredSharesCount'
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
        $share = FileShare::create([
            'user_id' => auth()->id(),
            'share_token' => Str::random(32),
            'title' => $title,
            'description' => $request->description,
            'expires_at' => Carbon::now()->addDays((int) $request->expires_days)->endOfDay(),
            'download_limit' => $request->download_limit,
            'password' => $request->password ? bcrypt($request->password) : null,
            'is_active' => true,
        ]);

        // Salva os arquivos e anexa ao compartilhamento
        foreach ($request->file('files') as $file) {
            $path = $file->store('shares', 'public');
            $share->items()->create([
                'filename' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getClientMimeType(),
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

        if ($request->has('password')) {
            $updateData['password'] = $request->password ? bcrypt($request->password) : null;
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
            Storage::disk('public')->delete($item->file_path);
        }

        $share->delete();

        return back()->with('success', 'Compartilhamento excluído com sucesso.');
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
        $path = storage_path('app/public/' . $item->file_path);

        if (!file_exists($path)) {
            abort(404, 'Arquivo não encontrado no servidor.');
        }

        $share->increment('download_count');

        return response()->download($path, $item->filename);
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

        $zipFile = tempnam(sys_get_temp_dir(), 'share_') . '.zip';
        $zip = new \ZipArchive();

        if ($zip->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            foreach ($share->items as $item) {
                $path = storage_path('app/public/' . $item->file_path);
                if (file_exists($path)) {
                    $zip->addFile($path, $item->filename);
                }
            }
            $zip->close();
        }

        $share->increment('download_count');

        $downloadName = Str::slug($share->title) . '-arquivos.zip';
        return response()->download($zipFile, $downloadName)->deleteFileAfterSend(true);
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
