<?php

namespace App\Http\Controllers;

use App\Models\Proposal;
use Illuminate\Http\Request;

class ProposalController extends Controller
{
    public function show($hash)
    {
        $proposal = Proposal::with('project.client.user')
            ->where(function($q) use ($hash) {
                $q->where('hash', $hash)->orWhere('custom_hash', $hash);
            })
            ->firstOrFail();
        
        return view('proposals.show', compact('proposal'));
    }

    public function approve($hash)
    {
        $proposal = Proposal::with('project')
            ->where(function($q) use ($hash) {
                $q->where('hash', $hash)->orWhere('custom_hash', $hash);
            })
            ->firstOrFail();
        
        $proposal->update(['status' => 'aprovado']);
        
        // Quando aprovado, movemos o status do projeto para "aprovado" automaticamente
        $proposal->project->update(['status' => 'aprovado']);

        // Cria notificação de proposta aprovada para o gestor
        \App\Models\Notification::create([
            'user_id' => $proposal->project->user_id ?? 1,
            'title' => 'Orçamento Aprovado',
            'content' => "O orçamento do projeto '" . $proposal->project->title . "' foi aprovado pelo cliente através do link público.",
            'type' => 'proposal'
        ]);

        return redirect()->back()->with('success', 'Orçamento aprovado com sucesso! O status foi atualizado para aprovado.');
    }

    public function reject($hash)
    {
        $proposal = Proposal::with('project')
            ->where(function($q) use ($hash) {
                $q->where('hash', $hash)->orWhere('custom_hash', $hash);
            })
            ->firstOrFail();
        
        $proposal->update(['status' => 'recusado']);
        
        // Quando rejeitado, movemos o status do projeto para "rejeitado" automaticamente
        $proposal->project->update(['status' => 'rejeitado']);

        return redirect()->back()->with('success', 'Orçamento rejeitado.');
    }
}
