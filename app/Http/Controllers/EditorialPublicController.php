<?php

namespace App\Http\Controllers;

use App\Models\EditorialRevision;
use App\Models\EditorialRevisionCorrection;
use App\Models\EditorialRevisionComment;
use Illuminate\Http\Request;

class EditorialPublicController extends Controller
{
    /**
     * Tela pública do Autor para visualizar o projeto de revisão, dúvidas e observações.
     */
    public function show(string $token)
    {
        $revision = EditorialRevision::where('share_token', $token)
            ->with(['files', 'corrections.comments', 'glossaries'])
            ->firstOrFail();

        // Se o autor abrir a página e houver status 'em_revisao', atualiza se houver dúvidas
        $duvidasCount = $revision->corrections->where('category', 'duvida')->count();

        return view('editorial_revisions.public_show', compact('revision', 'duvidasCount'));
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
            'user_id' => null,
            'author_name' => $name,
            'message' => $request->message,
        ]);

        $correction->update([
            'status' => 'respondida',
        ]);

        return back()->with('success', 'Sua resposta foi enviada ao revisor com sucesso!');
    }
}
