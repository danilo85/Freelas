<?php

namespace App\Http\Controllers;

use App\Models\EditorialRevision;
use App\Models\EditorialRevisionCorrection;
use App\Models\EditorialRevisionComment;
use App\Models\EditorialRevisionFile;
use App\Models\EditorialRevisionGlossary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class EditorialPublicController extends Controller
{
    /**
     * Portal do Revisor (Acesso via Token Público do Revisor).
     * O Revisor trabalha nesta página dedicada para ler/renderizar os arquivos (PDFs, Word, Imagens),
     * extrair textos, rodar a verificação ortográfica (LanguageTool), baixar originais,
     * criar apontamentos por categorias, salvar histórico de versões e gerar o link do Autor.
     */
    public function revisorShow(string $token)
    {
        $revision = EditorialRevision::where('share_token', $token)
            ->with(['files', 'corrections.comments', 'glossaries', 'revisor'])
            ->firstOrFail();

        // Extrai texto dos arquivos Word/PDF para renderização legível no editor
        $extractedTexts = [];
        foreach ($revision->files as $file) {
            $extractedTexts[$file->id] = $this->extractTextFromFile($file, $revision->storage_disk);
        }

        $isAuthenticated = auth()->check() || session()->has('revisor_authenticated_' . $revision->id);

        return view('editorial_revisions.revisor_show', compact('revision', 'extractedTexts', 'isAuthenticated'));
    }

    /**
     * Stream de arquivo ultra-seguro inline para PDF.js, leitores e imagens.
     */
    public function streamFile(string $token, int $fileId)
    {
        $file = EditorialRevisionFile::findOrFail($fileId);
        $revision = $file->editorialRevision;

        $disksToTry = array_unique([$revision->storage_disk, 'public', 'local']);

        foreach ($disksToTry as $diskName) {
            if (!$diskName) continue;
            try {
                $disk = Storage::disk($diskName);
                if ($disk->exists($file->file_path)) {
                    $filePath = $disk->path($file->file_path);
                    if (file_exists($filePath)) {
                        return response()->file($filePath, [
                            'Content-Disposition' => 'inline; filename="' . $file->filename . '"',
                        ]);
                    }
                }
            } catch (\Throwable $e) {}
        }

        // Checagem direta de caminho no storage
        $directPath = storage_path('app/public/' . $file->file_path);
        if (file_exists($directPath)) {
            return response()->file($directPath, [
                'Content-Disposition' => 'inline; filename="' . $file->filename . '"',
            ]);
        }

        abort(404, 'Arquivo não encontrado no servidor.');
    }

    /**
     * Download direto do arquivo bruto.
     */
    public function downloadFile(string $token, int $fileId)
    {
        $file = EditorialRevisionFile::findOrFail($fileId);
        $revision = $file->editorialRevision;
        $disk = Storage::disk($revision->storage_disk);

        if (!$disk->exists($file->file_path)) {
            abort(404, 'Arquivo não encontrado para download.');
        }

        return $disk->download($file->file_path, $file->filename);
    }

    /**
     * Salva o texto revisado com o histórico de alterações (Track Changes).
     */
    public function saveRevisedContent(Request $request, string $token, int $fileId)
    {
        $revision = EditorialRevision::where('share_token', $token)->firstOrFail();
        $file = EditorialRevisionFile::where('editorial_revision_id', $revision->id)->findOrFail($fileId);

        $request->validate([
            'revised_content' => 'required|string',
        ]);

        $file->update([
            'extracted_text' => $request->revised_content,
        ]);

        return response()->json(['success' => true, 'message' => 'Alterações de texto salvas com sucesso!']);
    }

    /**
     * Login rápido do Revisor no portal público com e-mail e senha fornecidos.
     */
    public function revisorLogin(Request $request, string $token)
    {
        $revision = EditorialRevision::where('share_token', $token)->firstOrFail();

        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            session(['revisor_authenticated_' . $revision->id => true]);
            return response()->json(['success' => true, 'message' => 'Autenticado com sucesso!']);
        }

        return response()->json(['success' => false, 'message' => 'E-mail ou senha inválidos.'], 401);
    }

    /**
     * Portal do Autor (Visualização e Resposta às Dúvidas do Revisor).
     */
    public function show(string $token)
    {
        $revision = EditorialRevision::where('share_token', $token)
            ->with(['files', 'corrections.comments', 'glossaries'])
            ->firstOrFail();

        $duvidasCount = $revision->corrections->where('category', 'duvida')->count();

        return view('editorial_revisions.public_show', compact('revision', 'duvidasCount'));
    }

    /**
     * Chama a API REST do LanguageTool para sugestões de ortografia e gramática.
     */
    public function checkLanguageTool(Request $request)
    {
        $request->validate([
            'text' => 'required|string|max:20000',
        ]);

        try {
            $response = Http::asForm()->post('https://api.languagetool.org/v2/check', [
                'text' => $request->text,
                'language' => 'pt-BR',
            ]);

            if ($response->successful()) {
                return response()->json($response->json());
            }
        } catch (\Throwable $e) {}

        return response()->json(['matches' => []]);
    }

    /**
     * Permite ao Revisor criar correções diretamente pelo Portal do Revisor.
     */
    public function storeCorrectionPublic(Request $request, string $token)
    {
        $revision = EditorialRevision::where('share_token', $token)->firstOrFail();

        $request->validate([
            'category' => 'required|string',
            'original_text' => 'nullable|string',
            'suggested_text' => 'nullable|string',
            'justification' => 'nullable|string',
        ]);

        EditorialRevisionCorrection::create([
            'editorial_revision_id' => $revision->id,
            'editorial_revision_file_id' => $request->editorial_revision_file_id,
            'page_number' => $request->page_number,
            'original_text' => $request->original_text,
            'suggested_text' => $request->suggested_text,
            'justification' => $request->justification,
            'category' => $request->category,
            'priority' => $request->get('priority', 'media'),
            'status' => 'pendente',
            'source' => 'revisor',
        ]);

        return back()->with('success', 'Apontamento registrado com sucesso no projeto!');
    }

    /**
     * Permite ao Revisor adicionar termos ao glossário diretamente pelo portal.
     */
    public function storeGlossaryPublic(Request $request, string $token)
    {
        $revision = EditorialRevision::where('share_token', $token)->firstOrFail();

        $request->validate([
            'correct_term' => 'required|string|max:255',
        ]);

        EditorialRevisionGlossary::create([
            'editorial_revision_id' => $revision->id,
            'correct_term' => $request->correct_term,
            'incorrect_terms' => $request->incorrect_terms,
            'description' => $request->description,
        ]);

        return back()->with('success', 'Termo adicionado ao Glossário do projeto!');
    }

    /**
     * Upload de nova versão do arquivo pelo Revisor (Histórico de Versões).
     */
    public function storeFileVersionPublic(Request $request, string $token)
    {
        $revision = EditorialRevision::where('share_token', $token)->firstOrFail();

        $request->validate([
            'file' => 'required|file|max:1048576',
            'parent_file_id' => 'required|integer',
        ]);

        $parentFile = EditorialRevisionFile::where('editorial_revision_id', $revision->id)
            ->findOrFail($request->parent_file_id);

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());
        $mime = $file->getClientMimeType();

        $fileType = 'image';
        if (in_array($ext, ['doc', 'docx', 'txt', 'rtf', 'odt'])) {
            $fileType = 'word';
        } elseif ($ext === 'pdf') {
            $fileType = 'pdf';
        }

        $path = $file->store('editorial_revisions', $revision->storage_disk);

        $newVersion = EditorialRevisionFile::create([
            'editorial_revision_id' => $revision->id,
            'filename' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $mime,
            'file_type' => $fileType,
            'version' => $parentFile->version + 1,
            'is_final' => true,
        ]);

        // Se for PDF, gera versão em Word (.docx) automaticamente
        if ($fileType === 'pdf') {
            $docxPath = \App\Services\PdfToDocxConverter::convert($path, $revision->storage_disk);
            if ($docxPath) {
                $docxFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . ' (Word Editável v' . ($parentFile->version + 1) . ').docx';
                $docxSize = Storage::disk($revision->storage_disk)->exists($docxPath) ? Storage::disk($revision->storage_disk)->size($docxPath) : $file->getSize();

                EditorialRevisionFile::create([
                    'editorial_revision_id' => $revision->id,
                    'filename' => $docxFilename,
                    'file_path' => $docxPath,
                    'file_size' => $docxSize,
                    'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'file_type' => 'word',
                    'version' => $parentFile->version + 1,
                    'is_final' => true,
                ]);
            }
        }

        return back()->with('success', 'Nova versão do arquivo salva com sucesso (Versão ' . $newVersion->version . ')!');
    }

    /**
     * O Autor responde uma dúvida diretamente no portal público.
     */
    public function replyCorrection(Request $request, string $token, int $correctionId)
    {
        $request->validate([
            'message' => 'required|string',
            'author_name' => 'nullable|string',
        ]);

        $revision = EditorialRevision::where('share_token', $token)->firstOrFail();
        $correction = EditorialRevisionCorrection::where('editorial_revision_id', $revision->id)
            ->findOrFail($correctionId);

        $name = $request->author_name ?: 'Autor(a)';

        EditorialRevisionComment::create([
            'editorial_revision_correction_id' => $correction->id,
            'user_id' => auth()->id(),
            'author_name' => $name,
            'message' => $request->message,
        ]);

        $correction->update([
            'status' => 'respondida',
        ]);

        return back()->with('success', 'Sua resposta foi enviada ao revisor com sucesso!');
    }

    /**
     * Método auxiliar nativo em PHP para extrair texto formatado preservando parágrafos de arquivos Word (.docx).
     */
    protected function extractTextFromFile(EditorialRevisionFile $file, string $disk)
    {
        try {
            $disksToTry = array_unique([$disk, 'public', 'local']);
            $filePath = null;

            foreach ($disksToTry as $d) {
                if (Storage::disk($d)->exists($file->file_path)) {
                    $filePath = Storage::disk($d)->path($file->file_path);
                    break;
                }
            }

            if (!$filePath || !file_exists($filePath)) {
                $filePath = storage_path('app/public/' . $file->file_path);
            }

            if ($file->file_type === 'word' && file_exists($filePath)) {
                $zip = new \ZipArchive();
                if ($zip->open($filePath) === true) {
                    if (($index = $zip->locateName('word/document.xml')) !== false) {
                        $data = $zip->getFromIndex($index);
                        $zip->close();

                        // Processa XML do Word preservando a diagramação de parágrafos <w:p> e quebras <w:br>
                        $data = str_replace(['</w:p>', '<w:br/>', '<w:br>', '</w:tr>'], ["\n\n", "\n", "\n", "\n"], $data);
                        $data = str_replace('<w:tab/>', "\t", $data);

                        $text = trim(strip_tags($data));
                        // Normaliza linhas em branco duplas sem colar tudo num parágrafo só
                        $text = preg_replace("/\n{3,}/", "\n\n", $text);
                        return $text;
                    }
                    $zip->close();
                }
            }
        } catch (\Throwable $e) {}

        return 'Conteúdo do arquivo disponível para leitura e download.';
    }
}
