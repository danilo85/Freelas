<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AssetController extends Controller
{
    /**
     * Exibe a listagem de assets com estatísticas e filtros.
     */
    public function index(Request $request)
    {
        $userId = auth()->id();
        $query = Asset::where('user_id', $userId);

        // Filtro por termo
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filtro por tipo
        if ($request->filled('type')) {
            $query->where('type', $request->get('type'));
        }

        $assets = $query->orderBy('created_at', 'desc')->get();

        // Estatísticas
        $totalStorage = 0;
        $imagesCount = 0;
        $fontsCount = 0;
        $codeCount = 0;
        $filesCount = 0;

        foreach ($assets as $asset) {
            if ($asset->file_size) {
                $totalStorage += $asset->file_size;
            }
            switch ($asset->type) {
                case 'imagem': $imagesCount++; break;
                case 'fonte': $fontsCount++; break;
                case 'codigo': $codeCount++; break;
                case 'arquivo': $filesCount++; break;
            }
        }

        return view('assets.index', compact(
            'assets',
            'totalStorage',
            'imagesCount',
            'fontsCount',
            'codeCount',
            'filesCount'
        ));
    }

    /**
     * Salva o recurso enviado.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'upload_type' => 'required|string|in:file,code',
            'file' => 'required_if:upload_type,file|nullable|file|max:307200', // 300MB max por arquivo individual no banco de assets
            'code_snippet' => 'required_if:upload_type,code|nullable|string',
        ]);

        $assetData = [
            'user_id' => auth()->id(),
            'title' => $request->title,
            'description' => $request->description,
        ];

        if ($request->upload_type === 'code') {
            $ext = $request->get('code_extension', 'txt');
            $title = $request->title;
            if (!preg_match('/\\.' . preg_quote($ext, '/') . '$/i', $title)) {
                $title .= '.' . $ext;
            }
            $assetData['title'] = $title;
            $assetData['type'] = 'codigo';
            $assetData['code_snippet'] = $request->code_snippet;
            $assetData['file_size'] = strlen($request->code_snippet);
            $assetData['mime_type'] = 'text/plain';
        } else {
            $file = $request->file('file');
            $extension = strtolower($file->getClientOriginalExtension());
            
            $imageExtensions = ['png', 'jpg', 'jpeg', 'gif', 'svg', 'webp'];
            $fontExtensions = ['ttf', 'otf', 'woff', 'woff2'];
            $codeExtensions = ['js', 'css', 'html', 'php', 'json', 'py', 'sh', 'xml', 'md', 'sql', 'ts', 'bat', 'cmd', 'ps1', 'yml', 'yaml', 'vue', 'rs', 'go', 'rb', 'java', 'cpp', 'c', 'h', 'cs', 'blade.php', 'blade', 'jsx', 'tsx', 'sass', 'scss', 'less', 'ini', 'env', 'config', 'gitignore'];

            if (in_array($extension, $imageExtensions)) {
                $type = 'imagem';
            } elseif (in_array($extension, $fontExtensions)) {
                $type = 'fonte';
            } elseif (in_array($extension, $codeExtensions)) {
                $type = 'codigo';
            } else {
                $type = 'arquivo';
            }

            // Para arquivos de código físicos enviados via upload, podemos salvar o conteúdo de texto no banco ou apenas o arquivo
            if ($type === 'codigo') {
                $content = file_get_contents($file->getRealPath());
                $assetData['code_snippet'] = $content;
            }

            $path = $file->store('assets', 'public');

            $assetData['type'] = $type;
            $assetData['file_path'] = $path;
            $assetData['file_size'] = $file->getSize();
            $assetData['mime_type'] = $file->getClientMimeType();
        }

        Asset::create($assetData);

        return redirect()->route('revisoes.assets.index')
            ->with('success', 'Recurso adicionado com sucesso ao seu banco de assets!');
    }

    /**
     * Atualiza o recurso.
     */
    public function update(Request $request, Asset $asset)
    {
        abort_if($asset->user_id !== auth()->id(), 403);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'code_snippet' => 'nullable|string',
        ]);

        $updateData = [
            'title' => $request->title,
            'description' => $request->description,
        ];

        if ($asset->type === 'codigo' && $request->has('code_snippet')) {
            $updateData['code_snippet'] = $request->code_snippet;
            $updateData['file_size'] = strlen($request->code_snippet);
        }

        $asset->update($updateData);

        return back()->with('success', 'Recurso atualizado com sucesso.');
    }

    /**
     * Remove o recurso.
     */
    public function destroy(Asset $asset)
    {
        abort_if($asset->user_id !== auth()->id(), 403);

        if ($asset->file_path) {
            Storage::disk('public')->delete($asset->file_path);
        }

        $asset->delete();

        return back()->with('success', 'Recurso removido com sucesso.');
    }

    /**
     * Oculta ou exibe um asset.
     */
    public function toggleVisibility(Asset $asset)
    {
        abort_if($asset->user_id !== auth()->id(), 403);

        $asset->update([
            'is_hidden' => !$asset->is_hidden
        ]);

        $msg = $asset->is_hidden ? 'Recurso ocultado com sucesso.' : 'Recurso exibido com sucesso.';
        return back()->with('success', $msg);
    }

    /**
     * Faz download do asset.
     */
    public function download(Asset $asset)
    {
        abort_if($asset->user_id !== auth()->id(), 403);

        if ($asset->type === 'codigo' && !$asset->file_path) {
            $extension = 'txt';
            if (preg_match('/\.([a-z0-9]+)$/i', $asset->title, $matches)) {
                $extension = strtolower($matches[1]);
            }
            $filename = pathinfo($asset->title, PATHINFO_EXTENSION) === $extension
                ? $asset->title
                : Str::slug(pathinfo($asset->title, PATHINFO_FILENAME)) . '.' . $extension;
            return response($asset->code_snippet, 200, [
                'Content-Type' => 'text/plain',
                'Content-Disposition' => "attachment; filename=\"{$filename}\""
            ]);
        }

        $path = storage_path('app/public/' . $asset->file_path);

        if (!file_exists($path)) {
            abort(404, 'Arquivo não localizado no servidor.');
        }

        $extension = pathinfo($asset->file_path, PATHINFO_EXTENSION);
        $filename = pathinfo($asset->title, PATHINFO_EXTENSION) === $extension
            ? $asset->title
            : Str::slug(pathinfo($asset->title, PATHINFO_FILENAME)) . '.' . $extension;

        return response()->download($path, $filename);
    }

    /**
     * Baixa múltiplos recursos em um ZIP em lote.
     */
    public function downloadBatch(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer',
        ]);

        $assets = Asset::where('user_id', auth()->id())->whereIn('id', $request->ids)->get();

        if ($assets->isEmpty()) {
            return back()->with('error', 'Nenhum recurso selecionado.');
        }

        $zipFile = tempnam(sys_get_temp_dir(), 'assets_') . '.zip';
        $zip = new \ZipArchive();

        if ($zip->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            foreach ($assets as $asset) {
                if ($asset->type === 'codigo' && $asset->code_snippet && !$asset->file_path) {
                    $extension = 'txt';
                    if (preg_match('/\.([a-z0-9]+)$/i', $asset->title, $matches)) {
                        $extension = strtolower($matches[1]);
                    }
                    $filename = pathinfo($asset->title, PATHINFO_EXTENSION) === $extension
                        ? $asset->title
                        : Str::slug(pathinfo($asset->title, PATHINFO_FILENAME)) . '.' . $extension;
                    $zip->addFromString($filename, $asset->code_snippet);
                } elseif ($asset->file_path) {
                    $path = storage_path('app/public/' . $asset->file_path);
                    if (file_exists($path)) {
                        $extension = pathinfo($asset->file_path, PATHINFO_EXTENSION);
                        $filename = pathinfo($asset->title, PATHINFO_EXTENSION) === $extension
                            ? $asset->title
                            : Str::slug(pathinfo($asset->title, PATHINFO_FILENAME)) . '.' . $extension;
                        $zip->addFile($path, $filename);
                    }
                }
            }
            $zip->close();
        }

        $downloadName = 'assets-selecionados-' . now()->format('YmdHis') . '.zip';
        return response()->download($zipFile, $downloadName)->deleteFileAfterSend(true);
    }

    /**
     * Exclui múltiplos recursos selecionados.
     */
    public function destroyBatch(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer',
        ]);

        $assets = Asset::where('user_id', auth()->id())->whereIn('id', $request->ids)->get();

        foreach ($assets as $asset) {
            if ($asset->file_path) {
                Storage::disk('public')->delete($asset->file_path);
            }
            $asset->delete();
        }

        return back()->with('success', 'Recursos excluídos com sucesso.');
    }
}
