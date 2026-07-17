<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\ProjectRevision;
use App\Models\RevisionRound;
use App\Models\RevisionFile;
use App\Models\RevisionAnnotation;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class PublicRevisionController extends Controller
{
    public function show(Request $request, $token)
    {
        $revision = ProjectRevision::where('share_token', $token)
            ->with(['author', 'project.authors', 'rounds.files.annotations'])
            ->firstOrFail();

        // Get the latest round number
        $latestRoundNumber = $revision->rounds()->max('round_number') ?? 1;

        // Get active round
        $activeRound = null;
        if ($request->filled('round')) {
            $activeRound = $revision->rounds()->where('round_number', $request->round)->first();
        }

        if (!$activeRound) {
            $activeRound = $revision->rounds()->where('round_number', $latestRoundNumber)->first();
        }

        $files = $activeRound ? $activeRound->files : collect();

        // Active File
        $activeFile = null;
        if ($request->filled('file')) {
            $activeFile = $files->where('id', $request->file)->first();
        } else {
            $activeFile = $files->first();
        }

        $annotations = $activeFile 
            ? $activeFile->annotations()->with('author')->orderBy('page_number', 'asc')->orderBy('created_at', 'asc')->get() 
            : collect();

        // Get authors list for select box
        $authors = $revision->project && $revision->project->authors->count() > 0
            ? $revision->project->authors
            : collect([$revision->author]);

        return view('revisions.public', compact('revision', 'activeRound', 'files', 'activeFile', 'annotations', 'authors', 'latestRoundNumber'));
    }

    public function storeAnnotation(Request $request, $fileId)
    {
        $request->validate([
            'comment' => 'required|string',
            'drawing_data' => 'nullable|string', // JSON coordinate points
            'page_number' => 'nullable|integer',
            'author_id' => 'nullable|exists:authors,id',
            'attachment' => 'nullable|file|max:102400', // 100MB limit
        ]);

        $file = RevisionFile::findOrFail($fileId);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('revisions/attachments', 'public');
        }

        $annotation = RevisionAnnotation::create([
            'revision_file_id' => $file->id,
            'page_number' => $request->page_number ?? 1,
            'drawing_data' => $request->drawing_data,
            'comment' => $request->comment,
            'status' => 'aberto',
            'author_id' => $request->author_id,
            'attachment_path' => $attachmentPath,
        ]);

        // Auto-progress round status to em_ajuste
        $round = $file->revisionRound;
        if ($round->status === 'pendente') {
            $round->update(['status' => 'em_ajuste']);
        }

        // Return with author details loaded
        $annotation->load('author');

        return response()->json([
            'success' => true,
            'annotation' => $annotation
        ]);
    }

    public function deleteAnnotation($annotationId)
    {
        $annotation = RevisionAnnotation::findOrFail($annotationId);
        $annotation->delete();

        return response()->json(['success' => true]);
    }

    public function resolveAnnotation($annotationId)
    {
        $annotation = RevisionAnnotation::findOrFail($annotationId);
        $newStatus = $annotation->status === 'aberto' ? 'resolvido' : 'aberto';
        $annotation->update(['status' => $newStatus]);

        return response()->json([
            'success' => true,
            'status' => $newStatus
        ]);
    }

    public function updateAnnotation(Request $request, $annotationId)
    {
        $request->validate([
            'comment' => 'required|string',
            'author_id' => 'nullable|exists:authors,id',
            'attachment' => 'nullable|file|max:102400', // 100MB limit
        ]);

        $annotation = RevisionAnnotation::findOrFail($annotationId);

        $data = [
            'comment' => $request->comment,
            'author_id' => $request->author_id,
        ];

        if ($request->hasFile('attachment')) {
            if ($annotation->attachment_path) {
                Storage::disk('public')->delete($annotation->attachment_path);
            }
            $data['attachment_path'] = $request->file('attachment')->store('revisions/attachments', 'public');
        }

        $annotation->update($data);
        $annotation->load('author');

        return response()->json([
            'success' => true,
            'annotation' => $annotation
        ]);
    }

    public function downloadFile($fileId)
    {
        $file = RevisionFile::findOrFail($fileId);

        if (!Storage::disk('public')->exists($file->file_path)) {
            abort(404, 'Arquivo não encontrado.');
        }

        return Storage::disk('public')->download($file->file_path, $file->filename);
    }

    public function downloadAllFiles($roundId)
    {
        $round = RevisionRound::with('files')->findOrFail($roundId);
        $files = $round->files;

        if ($files->isEmpty()) {
            return redirect()->back()->with('error', 'Nenhum arquivo nesta rodada para baixar.');
        }

        $zipFileName = 'revisao_rodada_' . $round->round_number . '_' . time() . '.zip';
        $zipFilePath = storage_path('app/public/' . $zipFileName);

        $zip = new ZipArchive();
        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            foreach ($files as $file) {
                if (Storage::disk('public')->exists($file->file_path)) {
                    $physicalPath = Storage::disk('public')->path($file->file_path);
                    
                    // Keep virtual folder name if set
                    $zipPath = $file->folder_name 
                        ? $file->folder_name . '/' . $file->filename 
                        : $file->filename;

                    $zip->addFile($physicalPath, $zipPath);
                }
            }
            $zip->close();
        } else {
            abort(500, 'Não foi possível gerar o arquivo ZIP.');
        }

        return response()->download($zipFilePath)->deleteFileAfterSend(true);
    }

    public function downloadAnnotationsReport($roundId)
    {
        $round = RevisionRound::with(['files.annotations', 'projectRevision'])->findOrFail($roundId);

        $revision = $round->projectRevision;
        $content = "RELATÓRIO DE ANOTAÇÕES E AJUSTES\n";
        $content .= "======================================\n";
        $content .= "Projeto: " . $revision->title . "\n";
        $content .= "Subtítulo: " . ($revision->subtitle ?? 'N/A') . "\n";
        $content .= "Rodada de Revisão: #" . $round->round_number . "\n";
        $content .= "Data de Exportação: " . date('d/m/Y H:i:s') . "\n";
        $content .= "======================================\n\n";

        $index = 1;
        foreach ($round->files as $file) {
            $annotations = $file->annotations->where('status', 'aberto');
            if ($annotations->isEmpty()) continue;

            $content .= "ARQUIVO: " . ($file->folder_name ? $file->folder_name . '/' : '') . $file->filename . "\n";
            $content .= "--------------------------------------\n";
            foreach ($annotations as $anno) {
                $content .= "Ajuste #{$index}\n";
                $content .= "- Página/Posição: Página " . $anno->page_number . "\n";
                $content .= "- Observação: " . $anno->comment . "\n";
                $content .= "- Status: " . ucfirst($anno->status) . "\n";
                $content .= "- Data: " . $anno->created_at->format('d/m/Y H:i') . "\n\n";
                $index++;
            }
            $content .= "\n";
        }

        if ($index === 1) {
            $content .= "Nenhum ajuste pendente registrado para esta rodada.\n";
        }

        $reportName = 'relatorio_ajustes_rodada_' . $round->round_number . '.txt';

        return response($content)
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', 'attachment; filename="' . $reportName . '"');
    }
}
