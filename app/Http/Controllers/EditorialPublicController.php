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
    public function streamFile(int $fileId)
    {
        $file = EditorialRevisionFile::findOrFail($fileId);
        $revision = $file->editorialRevision;
        $disk = Storage::disk($revision->storage_disk);

        if (!$disk->exists($file->file_path)) {
            abort(404, 'Arquivo não encontrado no servidor.');
        }

        try {
            $filePath = $disk->path($file->file_path);
            if (file_exists($filePath)) {
                return response()->file($filePath, [
                    'Content-Disposition' => 'inline; filename="' . $file->filename . '"',
                ]);
            }
        } catch (\Throwable $e) {}

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
     * Download direto do arquivo bruto.
     */
    public function downloadFile(int $fileId)
    {
        $file = EditorialRevisionFile::findOrFail($fileId);
        $revision = $file->editorialRevision;

        if (!Storage::disk($revision->storage_disk)->exists($file->file_path)) {
            abort(404, 'Arquivo não encontrado para download.');
        }

        return Storage::disk($revision->storage_disk)->download($file->file_path, $file->filename);
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
     * Método auxiliar nativo em PHP para extrair texto legível de arquivos Word (.docx) ou PDF.
     */
    protected function extractTextFromFile(EditorialRevisionFile $file, string $disk)
    {
        try {
            $filePath = Storage::disk($disk)->path($file->file_path);
            
            // Se o arquivo for .docx, lê o XML interno do arquivo ZIP
            if ($file->file_type === 'word' && file_exists($filePath)) {
                $zip = new \ZipArchive();
                if ($zip->open($filePath) === true) {
                    if (($index = $zip->locateName('word/document.xml')) !== false) {
                        $data = $zip->getFromIndex($index);
                        $zip->close();
                        $xml = new \DOMDocument();
                        @$xml->loadXML($data, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
                        return strip_tags($xml->saveXML());
                    }
                    $zip->close();
                }
            }
        } catch (\Throwable $e) {}

        return 'Conteúdo disponível para download em formato original.';
    }
}
