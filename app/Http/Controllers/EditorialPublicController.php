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
            'text' => 'required|string',
        ]);

        $plainText = trim(strip_tags($request->text));
        if (mb_strlen($plainText) > 10000) {
            $plainText = mb_substr($plainText, 0, 10000);
        }

        if (mb_strlen($plainText) < 2) {
            return response()->json(['matches' => []]);
        }

        $level = $request->input('level', 'default');

        try {
            $response = Http::asForm()->post('https://api.languagetool.org/v2/check', [
                'text' => $plainText,
                'language' => 'pt-BR',
                'level' => in_array($level, ['default', 'picky']) ? $level : 'default',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (!empty($data['matches'])) {
                    // Filtra falsos-positivos de fragmentos de sílabas de 1 ou 2 letras (como 'va', 'ca', 'ss')
                    $data['matches'] = array_values(array_filter($data['matches'], function ($match) {
                        if (isset($match['context']['length']) && $match['context']['length'] <= 2) {
                            $offset = $match['context']['offset'];
                            $len = $match['context']['length'];
                            $word = mb_strtolower(mb_substr($match['context']['text'], $offset, $len));
                            $validShortWords = ['de', 'em', 'um', 'ou', 'se', 'no', 'na', 'do', 'da', 'ao', 'às', 'os', 'as', 'já', 'há', 'fé', 'pó', 'pá', 'pé', 'nó', 'só', 'eu', 'tu', 'ele', 'nós', 'vós'];
                            if (!in_array($word, $validShortWords)) {
                                return false;
                            }
                        }
                        return true;
                    }));
                }
                return response()->json($data);
            }
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage(), 'matches' => []]);
        }

        return response()->json(['matches' => []]);
    }

    /**
     * Permite ao Revisor ou Autor enviar comentários em tempo real no Chat de Dúvidas via AJAX.
     */
    public function storeCommentPublic(Request $request, string $token, int $correctionId)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $revision = EditorialRevision::where('share_token', $token)->firstOrFail();
        $correction = EditorialRevisionCorrection::where('editorial_revision_id', $revision->id)
            ->findOrFail($correctionId);

        $senderName = auth()->check() ? auth()->user()->name : ($request->get('sender_name') ?: 'Autor(a)');

        $comment = EditorialRevisionComment::create([
            'editorial_revision_correction_id' => $correction->id,
            'user_id' => auth()->id(),
            'author_name' => $senderName,
            'message' => $request->message,
        ]);

        $correction->update([
            'status' => 'respondida',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Mensagem enviada no chat!',
            'comment' => [
                'id' => $comment->id,
                'author_name' => $comment->author_name,
                'message' => $comment->message,
                'created_at' => $comment->created_at->format('d/m/Y H:i'),
            ]
        ]);
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

        $correction = EditorialRevisionCorrection::create([
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

        $correction->load('comments');

        if ($request->wantsJson() || $request->ajax() || $request->isJson() || $request->header('Accept') === 'application/json') {
            return response()->json([
                'success' => true,
                'message' => 'Apontamento registrado com sucesso!',
                'correction' => $correction,
            ]);
        }

        return back()->with('success', 'Apontamento registrado com sucesso no projeto!');
    }

    /**
     * Atualiza os detalhes de uma dúvida/apontamento (trecho e pergunta/justificativa).
     */
    public function updateCorrectionPublic(Request $request, string $token, int $correctionId)
    {
        $revision = EditorialRevision::where('share_token', $token)->firstOrFail();
        $correction = EditorialRevisionCorrection::where('editorial_revision_id', $revision->id)->findOrFail($correctionId);

        $request->validate([
            'original_text' => 'nullable|string',
            'justification' => 'nullable|string',
        ]);

        $correction->update([
            'original_text' => $request->original_text,
            'justification' => $request->justification,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Dúvida/Trecho atualizado com sucesso!',
            'correction' => $correction,
        ]);
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
     * Retorna o HTML rico de um arquivo Word em tempo real via AJAX (prioriza o texto editado salvo no banco).
     */
    public function getFileTextContent(string $token, int $fileId)
    {
        $revision = EditorialRevision::where('share_token', $token)->firstOrFail();
        $file = $revision->files()->where('id', $fileId)->firstOrFail();

        // Se o arquivo já possui texto editado/salvo no banco de dados, retorna as edições do revisor
        if (!empty($file->extracted_text)) {
            return response()->json(['content' => $file->extracted_text]);
        }

        if ($file->file_type === 'word') {
            $html = \App\Services\DocxToHtmlConverter::convertToHtml($file->file_path, $revision->storage_disk ?: 'public');
            $file->update(['extracted_text' => $html]);
            return response()->json(['content' => $html]);
        }

        if ($file->file_type === 'pdf') {
            $html = \App\Services\PdfToHtmlConverter::convertToHtml($file->file_path, $revision->storage_disk ?: 'public');
            $file->update(['extracted_text' => $html]);
            return response()->json(['content' => $html]);
        }

        return response()->json(['content' => $file->extracted_text ?: '<p class="p-6 text-slate-500 italic">Sem conteúdo pré-extraído.</p>']);
    }

    /**
     * Método auxiliar para extrair HTML rico preservando formatação de arquivos Word (.docx) e PDF.
     */
    protected function extractTextFromFile(EditorialRevisionFile $file, string $disk)
    {
        if (!empty($file->extracted_text)) {
            return $file->extracted_text;
        }

        if ($file->file_type === 'word') {
            $html = \App\Services\DocxToHtmlConverter::convertToHtml($file->file_path, $disk);
            $file->update(['extracted_text' => $html]);
            return $html;
        }

        if ($file->file_type === 'pdf') {
            $html = \App\Services\PdfToHtmlConverter::convertToHtml($file->file_path, $disk);
            $file->update(['extracted_text' => $html]);
            return $html;
        }

        return '';
    }

    /**
     * Exporta o documento editado/revisado como arquivo .doc do Microsoft Word preservando marcações de categoria.
     */
    public function exportRevisedDocx(string $token, int $fileId)
    {
        $revision = EditorialRevision::where('share_token', $token)->firstOrFail();
        $file = $revision->files()->where('id', $fileId)->firstOrFail();

        $content = $file->extracted_text ?: $this->extractTextFromFile($file, $revision->storage_disk ?: 'public');

        // Transforma todas as tags <mark> em elementos com estilos inline reconhecidos nativamente pelo Microsoft Word
        $content = preg_replace_callback('/<mark([^>]*)>(.*?)<\/mark>/is', function($matches) {
            $attrs = $matches[1];
            $inner = $matches[2];
            
            $bgColor = '#fee2e2'; // Rose/Red padrão (Ortografia)
            $textColor = '#9f1239';
            $msoColor = 'red';

            if (str_contains($attrs, 'bg-amber-100') || str_contains(strtolower($attrs), 'gramat')) {
                $bgColor = '#fef3c7'; // Amarelo/Laranja (Gramática)
                $textColor = '#92400e';
                $msoColor = 'yellow';
            } elseif (str_contains($attrs, 'bg-cyan-100') || str_contains(strtolower($attrs), 'pontua')) {
                $bgColor = '#cffafe'; // Ciano/Azul (Pontuação)
                $textColor = '#155e75';
                $msoColor = 'cyan';
            } elseif (str_contains($attrs, 'bg-purple-100') || str_contains(strtolower($attrs), 'padroniz')) {
                $bgColor = '#f3e8ff'; // Roxo (Padronização)
                $textColor = '#6b21a8';
                $msoColor = 'magenta';
            } elseif (str_contains($attrs, 'bg-emerald-100') || str_contains(strtolower($attrs), 'duvida')) {
                $bgColor = '#d1fae5'; // Verde (Dúvida/Chat)
                $textColor = '#065f46';
                $msoColor = 'green';
            }

            return '<span style="background-color: ' . $bgColor . '; color: ' . $textColor . '; font-weight: bold; padding: 2px 5px; mso-highlight: ' . $msoColor . ';">' . $inner . '</span>';
        }, $content);

        $cleanFilename = pathinfo($file->filename, PATHINFO_FILENAME) . ' - Versão Revisada.doc';

        $wordHtml = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40">';
        $wordHtml .= '<head><meta charset="utf-8"><title>' . e($file->filename) . '</title>';
        $wordHtml .= '<!--[if gte mso 9]><xml><w:WordDocument><w:View>Print</w:View><w:Zoom>100</w:Zoom><w:DoNotOptimizeForBrowser/></w:WordDocument></xml><![endif]-->';
        $wordHtml .= '<style>';
        $wordHtml .= 'body { font-family: "Calibri", "Segoe UI", Arial, sans-serif; font-size: 11pt; padding: 2cm; line-height: 1.5; color: #1e293b; }';
        $wordHtml .= 'p { margin-bottom: 10pt; }';
        $wordHtml .= 'img { max-width: 100%; height: auto; margin: 10pt 0; }';
        $wordHtml .= 'mark, span[style*="mso-highlight"] { font-weight: bold; padding: 2px 5px; }';
        $wordHtml .= '</style></head><body>';
        $wordHtml .= $content;
        $wordHtml .= '</body></html>';

        return response($wordHtml)
            ->header('Content-Type', 'application/msword; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="' . $cleanFilename . '"')
            ->header('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
            ->header('Expires', '0');
    }

    /**
     * Exporta o relatório completo de apontamentos e histórico de correções (somente o que mudou por página).
     */
    public function exportCorrectionsReport(string $token)
    {
        $revision = EditorialRevision::where('share_token', $token)
            ->with(['files', 'corrections.file', 'corrections.comments'])
            ->firstOrFail();

        $filename = 'Relatorio_Apontamentos_Revisao_' . \Illuminate\Support\Str::slug($revision->title) . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($revision) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");

            fputcsv($file, ['ID', 'Arquivo', 'Página', 'Categoria', 'Trecho Original / Edição', 'Sugestão / Ajuste', 'Justificativa / Observação', 'Status', 'Data de Registro'], ';');

            foreach ($revision->corrections as $cor) {
                fputcsv($file, [
                    $cor->id,
                    $cor->file ? $cor->file->filename : 'Documento Geral',
                    $cor->page_number ?: 'N/A',
                    strtoupper($cor->category),
                    $cor->original_text ?: 'Edição no documento',
                    $cor->suggested_text ?: '',
                    $cor->justification ?: '',
                    strtoupper($cor->status),
                    $cor->created_at->format('d/m/Y H:i'),
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Exclui um apontamento de revisão no portal público do revisor.
     */
    public function destroyCorrectionPublic(string $token, int $correctionId)
    {
        $revision = EditorialRevision::where('share_token', $token)->firstOrFail();
        $correction = EditorialRevisionCorrection::where('editorial_revision_id', $revision->id)
            ->where('id', $correctionId)
            ->firstOrFail();

        $correction->delete();

        return response()->json(['success' => true, 'message' => 'Apontamento excluído com sucesso!']);
    }
}
