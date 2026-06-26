<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Project;
use App\Models\Proposal;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 0. Criar Usuários de Teste
        $userMaster = User::create([
            'name' => 'Admin Master',
            'email' => 'master@freela.com',
            'password' => Hash::make('password'),
            'role' => 'master',
            'theme_color' => 'green',
            'phone' => '(11) 99999-1111',
        ]);

        $userComum = User::create([
            'name' => 'Usuário Comum',
            'email' => 'comum@freela.com',
            'password' => Hash::make('password'),
            'role' => 'comum',
            'theme_color' => 'blue',
            'phone' => '(11) 99999-2222',
        ]);

        // 1. Criar Clientes associados aos respectivos usuários
        $client1 = Client::create([
            'user_id' => $userMaster->id,
            'name' => 'Acme Corporation',
            'email' => 'contact@acme.com',
            'phone' => '(11) 99999-8888',
            'document' => '12.345.678/0001-90',
        ]);

        $client2 = Client::create([
            'user_id' => $userMaster->id,
            'name' => 'Jane Doe Design',
            'email' => 'jane@doe.com',
            'phone' => '(21) 98888-7777',
            'document' => '123.456.789-00',
        ]);

        $client3 = Client::create([
            'user_id' => $userComum->id,
            'name' => 'StartUp X',
            'email' => 'hello@startupx.co',
            'phone' => '(31) 97777-6666',
            'document' => '98.765.432/0001-10',
        ]);

        // 2. Criar Projetos
        $project1 = Project::create([
            'title' => 'Novo Portal Corporativo',
            'description' => 'Desenvolvimento do site institucional utilizando Laravel e Tailwind CSS.',
            'client_id' => $client1->id,
            'status' => 'em andamento',
            'total_value' => 12000.00,
        ]);

        $project2 = Project::create([
            'title' => 'Sistema de Chamados',
            'description' => 'Área logada para gerenciamento de suporte e chamados de TI.',
            'client_id' => $client1->id,
            'status' => 'prospect',
            'total_value' => 8500.00,
        ]);

        $project3 = Project::create([
            'title' => 'Identidade Visual & Landing Page',
            'description' => 'Design de logo e desenvolvimento de Landing Page corporativa.',
            'client_id' => $client2->id,
            'status' => 'finalizado',
            'total_value' => 4500.00,
        ]);

        $project4 = Project::create([
            'title' => 'Aplicativo Mobile Webview',
            'description' => 'Empacotamento PWA com suporte a notificações push nativas.',
            'client_id' => $client3->id,
            'status' => 'prospect',
            'total_value' => 6000.00,
        ]);

        // 3. Criar Propostas/Orçamentos (Proposals)
        Proposal::create([
            'project_id' => $project1->id,
            'status' => 'aprovado',
            'hash' => 'proposal-portal-acme-12345',
        ]);

        Proposal::create([
            'project_id' => $project2->id,
            'status' => 'pendente',
            'hash' => 'proposal-chamados-acme-12345',
        ]);

        Proposal::create([
            'project_id' => $project3->id,
            'status' => 'aprovado',
            'hash' => 'proposal-jane-doe-12345',
        ]);

        Proposal::create([
            'project_id' => $project4->id,
            'status' => 'pendente',
            'hash' => 'proposal-startupx-12345',
        ]);

        // 4. Criar Transações (Financeiro)
        Transaction::create([
            'project_id' => $project1->id,
            'type' => 'entrada',
            'amount' => 6000.00,
            'paid_at' => Carbon::now()->day(5),
        ]);

        Transaction::create([
            'project_id' => $project1->id,
            'type' => 'entrada',
            'amount' => 3000.00,
            'paid_at' => Carbon::now()->subMonth()->day(15),
        ]);

        Transaction::create([
            'project_id' => $project3->id,
            'type' => 'entrada',
            'amount' => 4500.00,
            'paid_at' => Carbon::now()->day(10),
        ]);

        Transaction::create([
            'project_id' => $project3->id,
            'type' => 'saída',
            'amount' => 500.00,
            'paid_at' => Carbon::now()->day(12),
        ]);
    }
}
