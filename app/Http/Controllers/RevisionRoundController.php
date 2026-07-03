<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\ProjectRevision;
use App\Models\RevisionRound;
use App\Models\RevisionFile;
use Illuminate\Support\Facades\Storage;

class RevisionRoundController extends Controller
{
    public function storeRound(Request $request, $revisionId)
    {
        $request->validate([
            'description' => 'nullable|string',
        ]);

        $revision = ProjectRevision::where('user_id', auth()->id())->findOrFail($revisionId);

        // Find next round number
        $maxRound = $revision->rounds()->max('round_number') ?? 0;

        $revision->rounds()->create([
            'round_number' => $maxRound + 1,
            'description' => $request->description,
            'status' => 'pendente',
        ]);

        return redirect()->route('revisoes.show', $revision->id)
            ->with('success', 'Nova rodada de ajustes criada com sucesso.');
    }

    public function destroyRound($roundId)
    {
        $round = RevisionRound::whereHas('projectRevision', function ($q) {
            $q->where('user_id', auth()->id());
        })->findOrFail($roundId);

        // Delete all files physically from storage
        foreach ($round->files as $file) {
            Storage::disk('public')->delete($file->file_path);
        }

        $round->delete();

        return redirect()->back()->with('success', 'Rodada de ajustes excluída com sucesso.');
    }

    public function updateRoundStatus(Request $request, $roundId)
    {
        $request->validate([
            'status' => 'required|in:pendente,em_ajuste,aprovado',
        ]);

        $round = RevisionRound::whereHas('projectRevision', function ($q) {
            $q->where('user_id', auth()->id());
        })->findOrFail($roundId);

        $round->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'Status da rodada atualizado com sucesso.');
    }

    public function manageFiles($roundId)
    {
        $round = RevisionRound::whereHas('projectRevision', function ($q) {
            $q->where('user_id', auth()->id());
        })->with(['files.annotations', 'projectRevision.author', 'projectRevision.project'])->findOrFail($roundId);

        return view('revisions.files', compact('round'));
    }

    public function uploadFiles(Request $request, $roundId)
    {
        $request->validate([
            'files' => 'required|array',
            'files.*' => 'required|file|max:20480', // limit 20MB per file
            'folder_name' => 'nullable|string|max:100',
        ]);

        $round = RevisionRound::whereHas('projectRevision', function ($q) {
            $q->where('user_id', auth()->id());
        })->findOrFail($roundId);

        $folderName = trim($request->folder_name) ?: null;

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $filename = $file->getClientOriginalName();
                $extension = strtolower($file->getClientOriginalExtension());
                $size = $file->getSize();

                // Store inside revisions/round_{id}
                $path = $file->store("revisions/round_{$round->id}", 'public');

                RevisionFile::create([
                    'revision_round_id' => $round->id,
                    'folder_name' => $folderName,
                    'filename' => $filename,
                    'file_path' => $path,
                    'file_type' => $extension,
                    'file_size' => $size,
                ]);
            }
        }

        return redirect()->back()->with('success', 'Arquivos enviados com sucesso.');
    }

    public function deleteFile($fileId)
    {
        $file = RevisionFile::whereHas('revisionRound.projectRevision', function ($q) {
            $q->where('user_id', auth()->id());
        })->findOrFail($fileId);

        // Physical deletion
        if (Storage::disk('public')->exists($file->file_path)) {
            Storage::disk('public')->delete($file->file_path);
        }

        $file->delete();

        return redirect()->back()->with('success', 'Arquivo excluído com sucesso.');
    }
}
