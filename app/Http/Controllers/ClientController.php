<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClientController extends Controller
{
    /**
     * Exibe a listagem de clientes do usuário autenticado com estatísticas.
     */
    public function index()
    {
        $userId = auth()->id();
        
        // Carrega os clientes do usuário logado com seus projetos para calcular estatísticas
        $clients = auth()->user()->clients()->with('projects')->orderBy('name')->get()->map(function ($client) {
            $projects = $client->projects;
            $client->projects_count = $projects->count();
            $client->total_value = $projects->sum('total_value');
            $client->approved_count = $projects->where('status', 'aprovado')->count();
            $client->rejected_count = $projects->where('status', 'rejeitado')->count();
            return $client;
        });

        // 1. Total de Clientes
        $totalClientsCount = $clients->count();

        // 2. Clientes com Projetos Ativos ("em andamento")
        $clientsWithActiveProjectsCount = auth()->user()->clients()
            ->whereHas('projects', function ($q) {
                $q->where('status', 'em andamento');
            })->count();

        // 3. Novos Clientes (cadastrados nos últimos 30 dias)
        $newClientsCount = auth()->user()->clients()
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        // Eleger principais clientes (os 3 que mais têm projetos aprovados ou projetos totais)
        $sortedClients = $clients->sortByDesc('projects_count');
        $topClientIds = $sortedClients->where('projects_count', '>', 0)->take(3)->pluck('id')->toArray();

        // Ordenar colocando os principais clientes no topo do grid
        $clients = $clients->sort(function ($a, $b) use ($topClientIds) {
            $aTop = in_array($a->id, $topClientIds);
            $bTop = in_array($b->id, $topClientIds);
            if ($aTop && !$bTop) return -1;
            if (!$aTop && $bTop) return 1;
            return strcmp($a->name, $b->name);
        })->values();
        
        // 4. Detecção de clientes duplicados por nome
        $duplicates = Client::where('user_id', $userId)
            ->select('name', \DB::raw('count(*) as count'))
            ->groupBy('name')
            ->having('count', '>', 1)
            ->pluck('name');
        
        $suggestedDuplicates = [];
        if ($duplicates->count() > 0) {
            $suggestedDuplicates = Client::where('user_id', $userId)
                ->whereIn('name', $duplicates)
                ->withCount('projects')
                ->orderBy('name')
                ->get()
                ->groupBy('name');
        }

        return view('clients.index', compact(
            'clients',
            'totalClientsCount',
            'clientsWithActiveProjectsCount',
            'newClientsCount',
            'topClientIds',
            'suggestedDuplicates'
        ));
    }

    /**
     * Exibe o formulário de cadastro de cliente.
     */
    public function create()
    {
        return view('clients.create');
    }

    /**
     * Armazena um novo cliente associado ao usuário autenticado.
     */
    public function store(Request $request)
    {
        $userId = auth()->id();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:clients,email,NULL,id,user_id,' . $userId,
            'phone' => 'nullable|string',
            'document' => 'nullable|string',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
        }

        auth()->user()->clients()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'document' => $validated['document'],
            'avatar' => $avatarPath,
        ]);

        return redirect()->route('clients.index')->with('success', 'Cliente cadastrado com sucesso!');
    }

    /**
     * Exibe os detalhes de um cliente (garantindo que pertence ao usuário).
     */
    public function show(Client $client)
    {
        // Verificação de Segurança (tenancy check)
        abort_if($client->user_id !== auth()->id(), 403, 'Ação não autorizada.');

        // Carrega os projetos vinculados ao cliente
        $projects = $client->projects()->orderBy('created_at', 'desc')->get();

        return view('clients.show', compact('client', 'projects'));
    }

    /**
     * Exibe o formulário de edição de um cliente.
     */
    public function edit(Client $client)
    {
        // Verificação de Segurança (tenancy check)
        abort_if($client->user_id !== auth()->id(), 403, 'Ação não autorizada.');

        return view('clients.edit', compact('client'));
    }

    /**
     * Atualiza os dados de um cliente (garantindo que pertence ao usuário).
     */
    public function update(Request $request, Client $client)
    {
        $userId = auth()->id();
        
        // Verificação de Segurança (tenancy check)
        abort_if($client->user_id !== $userId, 403, 'Ação não autorizada.');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:clients,email,' . $client->id . ',id,user_id,' . $userId,
            'phone' => 'nullable|string',
            'document' => 'nullable|string',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $client->name = $validated['name'];
        $client->email = $validated['email'];
        $client->phone = $validated['phone'];
        $client->document = $validated['document'];

        if ($request->hasFile('avatar')) {
            // Remove o avatar anterior
            if ($client->avatar) {
                Storage::disk('public')->delete($client->avatar);
            }
            $client->avatar = $request->file('avatar')->store('avatars', 'public');
        }

        $client->registration_completed = true;
        $client->save();

        return redirect()->route('clients.index')->with('success', 'Cliente atualizado com sucesso!');
    }

    /**
     * Exclui um cliente (garantindo que pertence ao usuário).
     */
    public function destroy(Client $client)
    {
        // Verificação de Segurança (tenancy check)
        abort_if($client->user_id !== auth()->id(), 403, 'Ação não autorizada.');

        if ($client->avatar) {
            Storage::disk('public')->delete($client->avatar);
        }

        $client->delete();

        return redirect()->route('clients.index')->with('success', 'Cliente excluído com sucesso!');
    }

    /**
     * Exibe o extrato de pagamentos de forma pública e compartilhável para o cliente final.
     */
    public function publicStatement(string $shareToken)
    {
        $client = Client::where('share_token', $shareToken)->firstOrFail();

        // Carrega todos os projetos aprovados
        $projects = $client->projects()
            ->where('status', 'aprovado')
            ->orderBy('created_at', 'desc')
            ->get();

        // Carrega os pagamentos destes projetos
        $projectIds = $projects->pluck('id');
        $payments = \App\Models\Payment::whereIn('project_id', $projectIds)
            ->with('project')
            ->orderBy('paid_at', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        // Totais consolidados
        $totalEstimates = (float) $projects->sum('total_value');
        $totalPaid = (float) $payments->sum('amount');
        $totalRemaining = max(0.00, $totalEstimates - $totalPaid);
        $estimatesCount = $projects->count();

        return view('clients.public_statement', compact(
            'client',
            'projects',
            'payments',
            'totalEstimates',
            'totalPaid',
            'totalRemaining',
            'estimatesCount'
        ));
    }

    /**
     * Mescla perfis de clientes curados/selecionados pelo usuário.
     */
    public function merge(Request $request)
    {
        $userId = auth()->id();
        $validated = $request->validate([
            'main_client_id' => 'required|exists:clients,id',
            'duplicate_client_ids' => 'required|array',
            'duplicate_client_ids.*' => 'exists:clients,id',
        ]);

        $mainClient = Client::where('user_id', $userId)->findOrFail($validated['main_client_id']);

        $mergedNames = [];
        foreach ($validated['duplicate_client_ids'] as $dupId) {
            if ($dupId == $mainClient->id) continue;
            
            $dupClient = Client::where('user_id', $userId)->findOrFail($dupId);
            $mergedNames[] = $dupClient->name;

            // Move todos os projetos do cliente duplicado para o cliente principal
            \App\Models\Project::where('client_id', $dupClient->id)->update([
                'client_id' => $mainClient->id
            ]);

            // Se o cliente principal não possuir telefone/documento e o duplicado sim, podemos copiar
            if (empty($mainClient->phone) && !empty($dupClient->phone)) {
                $mainClient->phone = $dupClient->phone;
            }
            if (empty($mainClient->document) && !empty($dupClient->document)) {
                $mainClient->document = $dupClient->document;
            }
            if (empty($mainClient->email) && !empty($dupClient->email)) {
                $mainClient->email = $dupClient->email;
            }

            // Exclui o avatar do duplicado se houver antes de apagar o registro
            if ($dupClient->avatar && Storage::disk('public')->exists($dupClient->avatar)) {
                Storage::disk('public')->delete($dupClient->avatar);
            }

            $dupClient->delete();
        }

        $mainClient->save();

        return redirect()->route('clients.index')->with('success', 'Perfis de clientes mesclados com sucesso! Os orçamentos/trabalhos associados foram todos unificados no perfil principal.');
    }
}
