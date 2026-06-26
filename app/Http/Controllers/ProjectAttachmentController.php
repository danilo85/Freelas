<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Exception;

class ProjectAttachmentController extends Controller
{
    /**
     * Realiza o upload de um anexo para o projeto e salva no banco de dados.
     */
    public function store(Request $request, Project $project)
    {
        // Tenancy Check: Verificar se o orçamento pertence ao usuário autenticado
        abort_if($project->client->user_id !== auth()->id(), 403, 'Ação não autorizada.');

        $request->validate([
            'file' => 'required|file|max:10240', // Max 10MB
            'classification' => 'required|string|in:auto,anexo,nota_fiscal,material',
        ]);

        try {
            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $mimeType = $file->getClientMimeType() ?? $file->getMimeType();
            $fileSize = $file->getSize();

            // Salva no disco privado local
            $path = $file->store('attachments/' . $project->id, 'local');

            // Determinar classificação
            $classification = $request->classification;
            if ($classification === 'auto') {
                $classification = $this->autoClassify($originalName, $mimeType);
            }

            // Criar registro no banco
            $attachment = $project->attachments()->create([
                'name' => $originalName,
                'file_path' => $path,
                'file_size' => $fileSize,
                'mime_type' => $mimeType,
                'classification' => $classification,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Arquivo enviado com sucesso!',
                'attachment' => [
                    'id' => $attachment->id,
                    'name' => $attachment->name,
                    'file_size' => $attachment->file_size_formatted,
                    'classification' => $attachment->classification,
                    'download_url' => route('projects.attachments.download', $attachment->id),
                    'destroy_url' => route('projects.attachments.destroy', $attachment->id),
                    'created_at' => $attachment->created_at->format('d/m/Y H:i'),
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao enviar arquivo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Altera a classificação de um anexo dinamicamente.
     */
    public function updateClassification(Request $request, ProjectAttachment $attachment)
    {
        // Tenancy Check
        abort_if($attachment->project->client->user_id !== auth()->id(), 403, 'Ação não autorizada.');

        $validated = $request->validate([
            'classification' => 'required|string|in:anexo,nota_fiscal,material',
        ]);

        $attachment->update([
            'classification' => $validated['classification']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Classificação atualizada com sucesso!'
        ]);
    }

    /**
     * Faz o download seguro do arquivo.
     */
    public function download(ProjectAttachment $attachment)
    {
        // Tenancy Check
        abort_if($attachment->project->client->user_id !== auth()->id(), 403, 'Ação não autorizada.');

        // Verifica se o arquivo existe fisicamente
        if (!Storage::disk('local')->exists($attachment->file_path)) {
            abort(404, 'Arquivo não encontrado no servidor.');
        }

        return Storage::disk('local')->download($attachment->file_path, $attachment->name);
    }

    /**
     * Remove o arquivo do storage e o registro do banco de dados.
     */
    public function destroy(ProjectAttachment $attachment)
    {
        // Tenancy Check
        abort_if($attachment->project->client->user_id !== auth()->id(), 403, 'Ação não autorizada.');

        try {
            // Remove o arquivo físico
            if (Storage::disk('local')->exists($attachment->file_path)) {
                Storage::disk('local')->delete($attachment->file_path);
            }

            // Remove do banco
            $attachment->delete();

            return response()->json([
                'success' => true,
                'message' => 'Arquivo excluído com sucesso!'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir o arquivo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Classifica automaticamente com base no nome e MIME type.
     */
    private function autoClassify(string $filename, string $mimeType): string
    {
        $filenameLower = mb_strtolower($filename);
        $extension = pathinfo($filenameLower, PATHINFO_EXTENSION);
        
        // 1. Termos de Notas Fiscais, recibos, etc.
        $taxTerms = ['nota', 'fiscal', 'nf', 'nfe', 'xml', 'danfe', 'invoice', 'recibo', 'fatura'];
        foreach ($taxTerms as $term) {
            if (str_contains($filenameLower, $term)) {
                return 'nota_fiscal';
            }
        }
        if ($extension === 'xml') {
            return 'nota_fiscal';
        }

        // 2. Termos de Materiais de Projeto (Palavras-chave contextuais no nome)
        $materialKeywords = [
            'briefing', 'layout', 'design', 'logo', 'material', 'referencia', 'referência',
            'imagem', 'foto', 'screenshot', 'mockup', 'sketch', 'requisito'
        ];
        foreach ($materialKeywords as $keyword) {
            if (str_contains($filenameLower, $keyword)) {
                return 'material';
            }
        }

        // 3. Extensões típicas de Material de Projeto
        $materialExtensions = [
            'zip', 'rar', '7z', 'tar', 'gz',
            'pdf', 'psd', 'ai', 'fig', 'xd', 'sketch',
            'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
            'png', 'jpg', 'jpeg', 'webp', 'svg', 'gif'
        ];
        if (in_array($extension, $materialExtensions)) {
            return 'material';
        }
        
        if (str_starts_with($mimeType, 'image/')) {
            return 'material';
        }

        return 'anexo';
    }
}
