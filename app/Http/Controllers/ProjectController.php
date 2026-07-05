<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Client;
use App\Models\Author;
use App\Models\Proposal;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Exibe a listagem de projetos/orçamentos com estatísticas.
     */
    public function index()
    {
        $userId = auth()->id();

        // Carrega projetos pertencentes a clientes do usuário autenticado
        $projects = Project::whereHas('client', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->with(['client', 'authors'])->orderBy('created_at', 'desc')->get();

        // 1. Total de Orçamentos
        $totalProjectsCount = $projects->count();

        // 2. Valor Total Aprovado (Status: aprovado, quitado, finalizado)
        $approvedValue = $projects->whereIn('status', ['aprovado', 'quitado', 'finalizado'])->sum('total_value');

        // Total somado dos orçamentos aprovados a receber
        $approvedRemainingBalance = $projects->whereIn('status', ['aprovado', 'finalizado'])->sum(function($p) {
            return $p->remaining_balance;
        });

        // 3. Orçamentos em Análise
        $analyzingCount = $projects->where('status', 'analisando')->count();

        return view('projects.index', compact(
            'projects',
            'totalProjectsCount',
            'approvedValue',
            'approvedRemainingBalance',
            'analyzingCount'
        ));
    }

    /**
     * Exibe o formulário de cadastro de projeto/orçamento.
     */
    public function create()
    {
        // Clientes e Autores do usuário logado para carregar nos autocompletes
        $clients = auth()->user()->clients()->orderBy('name')->get();
        $authors = auth()->user()->authors()->orderBy('name')->get();

        return view('projects.create', compact('clients', 'authors'));
    }

    /**
     * Armazena um novo projeto/orçamento no banco de dados.
     */
    public function store(Request $request)
    {
        $userId = auth()->id();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'client_id' => 'nullable|integer',
            'new_client_name' => 'nullable|string|max:255',
            'total_value' => 'required|string',
            'initial_payment_percent' => 'required|integer|min:10|max:100',
            'term' => 'required|string|max:255',
            'budget_date' => 'required|date',
            'expiration_date' => 'required|date',
            'status' => 'required|in:rascunho,analisando,aprovado,rejeitado,quitado,finalizado',
            'author_ids' => 'nullable|array',
            'author_ids.*' => 'integer',
            'new_author_names' => 'nullable|array',
            'new_author_names.*' => 'string|max:255',
            'additional_info' => 'nullable|string',
        ]);

        // Limpeza do valor total monetário brasileiro (R$ 1.500,00 -> 1500.00)
        $cleanValue = $request->total_value;
        $cleanValue = str_replace('R$', '', $cleanValue);
        $cleanValue = trim($cleanValue);
        $cleanValue = str_replace('.', '', $cleanValue);
        $cleanValue = str_replace(',', '.', $cleanValue);
        $totalValue = (float) $cleanValue;

        // Resolução do cliente (existente ou cadastro on-the-fly)
        $clientId = null;
        if (!empty($validated['client_id'])) {
            $client = auth()->user()->clients()->findOrFail($validated['client_id']);
            $clientId = $client->id;
        } elseif (!empty($validated['new_client_name'])) {
            $client = auth()->user()->clients()->create([
                'name' => $validated['new_client_name'],
                'email' => 'cliente-temp-' . uniqid() . '@pendente.com.br',
                'registration_completed' => false,
            ]);
            $clientId = $client->id;
        } else {
            return back()->withErrors(['client_id' => 'Selecione um cliente ou informe um novo nome para cadastrar.'])->withInput();
        }

        // Criar o projeto
        $project = Project::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'client_id' => $clientId,
            'status' => $validated['status'],
            'total_value' => $totalValue,
            'initial_payment_percent' => $validated['initial_payment_percent'],
            'term' => $validated['term'],
            'budget_date' => $validated['budget_date'],
            'expiration_date' => $validated['expiration_date'],
            'additional_info' => $validated['additional_info'] ?? null,
        ]);

        // Resolução dos autores (existentes e novos on-the-fly)
        $authorIds = [];
        if (!empty($validated['author_ids'])) {
            $authorIds = auth()->user()->authors()->whereIn('id', $validated['author_ids'])->pluck('id')->toArray();
        }
        if (!empty($validated['new_author_names'])) {
            foreach ($validated['new_author_names'] as $newName) {
                if (trim($newName) !== '') {
                    $newAuthor = auth()->user()->authors()->create([
                        'name' => $newName,
                        'email' => 'autor-temp-' . uniqid() . '@pendente.com.br',
                        'registration_completed' => false,
                    ]);
                    $authorIds[] = $newAuthor->id;
                }
            }
        }

        $project->authors()->sync($authorIds);

        // Auto-create proposal for sharing
        $project->proposals()->create([
            'status' => 'pendente'
        ]);

        return redirect()->route('projects.index')->with('success', 'Orçamento cadastrado com sucesso!');
    }

    public function show(Project $project)
    {
        // Tenancy Check
        abort_if($project->client->user_id !== auth()->id(), 403, 'Ação não autorizada.');

        $project->load(['client', 'authors', 'attachments' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }, 'payments']);

        $proposal = $project->proposals()->firstOrCreate(
            ['project_id' => $project->id],
            ['status' => 'pendente']
        );

        $histories = $project->histories()->orderBy('created_at', 'desc')->get();

        $activeVersion = null;
        if (request()->has('version_id')) {
            $activeVersion = $project->histories()->findOrFail(request()->version_id);
            
            // Substitui temporariamente os atributos para visualização
            $project->title = $activeVersion->title;
            $project->description = $activeVersion->description;
            $project->total_value = $activeVersion->total_value;
            $project->initial_payment_percent = $activeVersion->initial_payment_percent;
            $project->term = $activeVersion->term;
            $project->budget_date = $activeVersion->budget_date;
            $project->expiration_date = $activeVersion->expiration_date;
            $project->additional_info = $activeVersion->additional_info;
            $project->status = $activeVersion->status;
        }

        return view('projects.show', compact('project', 'proposal', 'histories', 'activeVersion'));
    }

    /**
     * Exibe o formulário de edição de um projeto/orçamento.
     */
    public function edit(Project $project)
    {
        // Tenancy Check
        abort_if($project->client->user_id !== auth()->id(), 403, 'Ação não autorizada.');

        $project->load('authors');
        
        $clients = auth()->user()->clients()->orderBy('name')->get();
        $authors = auth()->user()->authors()->orderBy('name')->get();

        return view('projects.edit', compact('project', 'clients', 'authors'));
    }

    /**
     * Atualiza os dados do projeto/orçamento.
     */
    public function update(Request $request, Project $project)
    {
        // Tenancy Check
        abort_if($project->client->user_id !== auth()->id(), 403, 'Ação não autorizada.');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'client_id' => 'nullable|integer',
            'new_client_name' => 'nullable|string|max:255',
            'total_value' => 'required|string',
            'initial_payment_percent' => 'required|integer|min:10|max:100',
            'term' => 'required|string|max:255',
            'budget_date' => 'required|date',
            'expiration_date' => 'required|date',
            'status' => 'required|in:rascunho,analisando,aprovado,rejeitado,quitado,finalizado',
            'author_ids' => 'nullable|array',
            'author_ids.*' => 'integer',
            'new_author_names' => 'nullable|array',
            'new_author_names.*' => 'string|max:255',
            'additional_info' => 'nullable|string',
        ]);

        // Limpeza do valor total monetário brasileiro
        $cleanValue = $request->total_value;
        $cleanValue = str_replace('R$', '', $cleanValue);
        $cleanValue = trim($cleanValue);
        $cleanValue = str_replace('.', '', $cleanValue);
        $cleanValue = str_replace(',', '.', $cleanValue);
        $totalValue = (float) $cleanValue;

        // Resolução do cliente
        $clientId = null;
        if (!empty($validated['client_id'])) {
            $client = auth()->user()->clients()->findOrFail($validated['client_id']);
            $clientId = $client->id;
        } elseif (!empty($validated['new_client_name'])) {
            $client = auth()->user()->clients()->create([
                'name' => $validated['new_client_name'],
                'email' => 'cliente-temp-' . uniqid() . '@pendente.com.br',
                'registration_completed' => false,
            ]);
            $clientId = $client->id;
        } else {
            return back()->withErrors(['client_id' => 'Selecione um cliente ou informe um novo nome para cadastrar.'])->withInput();
        }

        // Trava 1: se houve pagamento registrado nesse orcamento, nao pode voltar a analisando
        if ($validated['status'] === 'analisando' && $project->payments()->count() > 0) {
            return back()->withErrors(['status' => 'Não é possível alterar o status para Analisando pois existem pagamentos registrados para este orçamento.'])->withInput();
        }

        // Atualizar o projeto
        $project->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'client_id' => $clientId,
            'status' => $validated['status'],
            'total_value' => $totalValue,
            'initial_payment_percent' => $validated['initial_payment_percent'],
            'term' => $validated['term'],
            'budget_date' => $validated['budget_date'],
            'expiration_date' => $validated['expiration_date'],
            'additional_info' => $validated['additional_info'] ?? null,
        ]);

        // Resolução dos autores
        $authorIds = [];
        if (!empty($validated['author_ids'])) {
            $authorIds = auth()->user()->authors()->whereIn('id', $validated['author_ids'])->pluck('id')->toArray();
        }
        if (!empty($validated['new_author_names'])) {
            foreach ($validated['new_author_names'] as $newName) {
                if (trim($newName) !== '') {
                    $newAuthor = auth()->user()->authors()->create([
                        'name' => $newName,
                        'email' => 'autor-temp-' . uniqid() . '@pendente.com.br',
                        'registration_completed' => false,
                    ]);
                    $authorIds[] = $newAuthor->id;
                }
            }
        }

        $project->authors()->sync($authorIds);

        return redirect()->route('projects.index')->with('success', 'Orçamento atualizado com sucesso!');
    }

    /**
     * Atualiza o status do projeto/orçamento via API (JSON).
     */
    public function updateStatus(Request $request, Project $project)
    {
        // Tenancy Check
        abort_if($project->client->user_id !== auth()->id(), 403, 'Ação não autorizada.');

        $validated = $request->validate([
            'status' => 'required|in:rascunho,analisando,aprovado,rejeitado,quitado,finalizado',
        ]);

        // Trava 1: se houve pagamento registrado nesse orcamento, nao pode voltar a analisando
        if ($validated['status'] === 'analisando' && $project->payments()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Não é possível alterar o status para Analisando pois existem pagamentos registrados para este orçamento.'
            ], 422);
        }

        $project->update([
            'status' => $validated['status'],
        ]);

        return response()->json([
            'success' => true,
            'status' => $project->status,
        ]);
    }

    /**
     * Exclui o projeto/orçamento.
     */
    public function destroy(Project $project)
    {
        // Tenancy Check
        abort_if($project->client->user_id !== auth()->id(), 403, 'Ação não autorizada.');

        $project->delete();

        return redirect()->route('projects.index')->with('success', 'Orçamento excluído com sucesso!');
    }

    /**
     * Importa propostas a partir do JSON Giro.
     */
    public function importJson(Request $request)
    {
        $request->validate([
            'json_data' => 'required|string',
        ]);

        $decoded = json_decode($request->json_data, true);

        if (!$decoded || !isset($decoded['format']) || !str_starts_with($decoded['format'], 'giro.orcamentos')) {
            return response()->json([
                'success' => false,
                'message' => 'O formato do JSON não é compatível ou não é um export do Giro válido.'
            ], 422);
        }

        $items = [];
        if (isset($decoded['data']['orcamento'])) {
            $items[] = $decoded['data'];
        } elseif (isset($decoded['data']) && is_array($decoded['data'])) {
            $items = $decoded['data'];
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Não foi possível encontrar a estrutura de orçamentos no JSON.'
            ], 422);
        }

        $user = auth()->user();
        $importedCount = 0;

        \Illuminate\Support\Facades\DB::beginTransaction();

        try {
            foreach ($items as $item) {
                if (!isset($item['orcamento'])) {
                    continue;
                }

                $orcamento = $item['orcamento'];
                $clientData = $item['cliente'] ?? null;
                $authorsData = $item['autores'] ?? [];
                $paymentsData = $item['pagamentos'] ?? [];
                $historyData = $item['historico'] ?? [];

                // 1. Process client
                $clientId = null;
                if ($clientData) {
                    $clientEmail = (!empty($clientData['email'])) ? $clientData['email'] : 'cliente-sem-email-' . uniqid() . '@freela.com';
                    $client = \App\Models\Client::where('email', $clientEmail)
                        ->where('user_id', $user->id)
                        ->first();
                    if (!$client) {
                        $client = \App\Models\Client::create([
                            'user_id' => $user->id,
                            'name' => $clientData['nome'],
                            'email' => $clientEmail,
                            'avatar' => null,
                            'phone' => $clientData['telefone'] ?? $clientData['whatsapp'] ?? null,
                            'registration_completed' => true
                        ]);
                    }
                    $clientId = $client->id;
                }

                // 2. Create Project
                $project = Project::create([
                    'title' => $orcamento['titulo'],
                    'description' => $orcamento['descricao'],
                    'client_id' => $clientId,
                    'status' => $orcamento['status'] ?? 'analisando',
                    'total_value' => $orcamento['valor_total'],
                    'term' => $orcamento['prazo_dias'],
                    'budget_date' => $orcamento['data_orcamento'],
                    'expiration_date' => $orcamento['data_validade'],
                    'additional_info' => $orcamento['observacoes'],
                ]);

                // 3. Create Proposal Token Hash
                Proposal::create([
                    'project_id' => $project->id,
                    'hash' => $orcamento['token_publico'] ?? bin2hex(random_bytes(16)),
                    'status' => $project->status
                ]);

                // 4. Authors relationship
                if (!empty($authorsData)) {
                    $authorIds = [];
                    foreach ($authorsData as $authorData) {
                        $authorEmail = (!empty($authorData['email'])) ? $authorData['email'] : 'autor-importado-' . uniqid() . '@pendente.com';
                        $author = \App\Models\Author::where('user_id', $user->id)
                            ->where(function($query) use ($authorData, $authorEmail) {
                                $query->where('email', $authorEmail)
                                      ->orWhere('name', $authorData['nome']);
                            })
                            ->first();

                        if (!$author) {
                            $author = \App\Models\Author::create([
                                'user_id' => $user->id,
                                'name' => $authorData['nome'],
                                'email' => $authorEmail,
                                'avatar' => null,
                                'phone' => $authorData['telefone'] ?? $authorData['whatsapp'] ?? null,
                                'registration_completed' => true
                            ]);
                        }
                        $authorIds[] = $author->id;
                    }
                    $project->authors()->sync($authorIds);
                }

                // 5. Payments
                if (!empty($paymentsData)) {
                    foreach ($paymentsData as $paymentData) {
                        \App\Models\Payment::create([
                            'project_id' => $project->id,
                            'amount' => $paymentData['valor'],
                            'paid_at' => $paymentData['data_pagamento'],
                            'payment_method' => $paymentData['metodo'] ?? 'transferência',
                            'observations' => $paymentData['observacoes'] ?? null
                        ]);
                    }
                }

                // 6. Histories
                if (!empty($historyData)) {
                    $project->histories()->delete();

                    foreach ($historyData as $hist) {
                        \App\Models\ProjectHistory::create([
                            'project_id' => $project->id,
                            'user_id' => $user->id,
                            'action' => $hist['acao'],
                            'title' => $project->title,
                            'description' => $hist['descricao'],
                            'total_value' => $project->total_value,
                            'term' => $project->term,
                            'budget_date' => $project->budget_date,
                            'expiration_date' => $project->expiration_date,
                            'status' => $project->status,
                            'created_at' => $hist['created_at']
                        ]);
                    }
                }

                $importedCount++;
            }

            \Illuminate\Support\Facades\DB::commit();

            session()->flash('success', "Importação realizada com sucesso! {$importedCount} orçamento(s) importado(s).");

            return response()->json([
                'success' => true,
                'message' => "Importação realizada com sucesso! {$importedCount} orçamento(s) importado(s)."
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro durante a importação: ' . $e->getMessage()
            ], 500);
        }
    }
}
