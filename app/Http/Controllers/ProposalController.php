<?php

namespace App\Http\Controllers;

use App\Models\Proposal;
use Illuminate\Http\Request;

class ProposalController extends Controller
{
    public function show($hash)
    {
        $proposal = Proposal::with('project.client')->where('hash', $hash)->firstOrFail();
        
        return view('proposals.show', compact('proposal'));
    }

    public function approve($hash)
    {
        $proposal = Proposal::with('project')->where('hash', $hash)->firstOrFail();
        
        $proposal->update(['status' => 'aprovado']);
        
        // Quando aprovado, movemos o status do projeto para "aprovado" automaticamente
        $proposal->project->update(['status' => 'aprovado']);

        return redirect()->back()->with('success', 'Orçamento aprovado com sucesso! O status foi atualizado para aprovado.');
    }

    public function reject($hash)
    {
        $proposal = Proposal::with('project')->where('hash', $hash)->firstOrFail();
        
        $proposal->update(['status' => 'recusado']);
        
        // Quando rejeitado, movemos o status do projeto para "rejeitado" automaticamente
        $proposal->project->update(['status' => 'rejeitado']);

        return redirect()->back()->with('success', 'Orçamento rejeitado.');
    }
}
