<?php
/**
 * Script de Limpeza de Dados de Usuário (Laravel)
 * Remove todos os dados de clientes, projetos, finanças, revisões, kanban, etc.
 * Mantém o cadastro do usuário (login e senha) intacto.
 * 
 * Uso: php clear_user.php [email_do_usuario]
 */

if (php_sapi_name() !== 'cli') {
    die("Este script só pode ser executado via linha de comando.");
}

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;

// Determina o e-mail do usuário
$email = $argv[1] ?? null;

if (!$email) {
    // Tenta encontrar o primeiro master por padrão
    $masterUser = User::where('role', 'master')->first();
    if ($masterUser) {
        $email = $masterUser->email;
    }
}

if (!$email) {
    echo "Erro: E-mail do usuário não fornecido e nenhum usuário master encontrado no banco.\n";
    echo "Uso: php clear_user.php usuario@email.com\n";
    exit(1);
}

$user = User::where('email', $email)->first();

if (!$user) {
    echo "Erro: Usuário com o e-mail '{$email}' não encontrado.\n";
    exit(1);
}

$userId = $user->id;
echo "---------------------------------------------------------\n";
echo "LIMPANDO DADOS DO USUÁRIO: {$user->name} ({$email})\n";
echo "Esta operação apagará todos os projetos, finanças e cadastros desse usuário.\n";
echo "O login do usuário NÃO será apagado.\n";
echo "---------------------------------------------------------\n";

try {
    DB::transaction(function () use ($userId) {
        $driver = DB::connection()->getDriverName();
        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF;');
        } elseif ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS = 0;');
        }

        // 1. Revisões de Trabalhos (Anotações, Arquivos, Rodadas, Revisões)
        $revisionIds = DB::table('project_revisions')->where('user_id', $userId)->pluck('id');
        $roundIds = DB::table('revision_rounds')->whereIn('project_revision_id', $revisionIds)->pluck('id');
        $fileIds = DB::table('revision_files')->whereIn('revision_round_id', $roundIds)->pluck('id');
        
        DB::table('revision_annotations')->whereIn('revision_file_id', $fileIds)->delete();
        DB::table('revision_files')->whereIn('revision_round_id', $roundIds)->delete();
        DB::table('revision_rounds')->whereIn('project_revision_id', $revisionIds)->delete();
        DB::table('project_revisions')->where('user_id', $userId)->delete();
        echo "- Revisões e rodadas de prova removidas.\n";

        // 2. Projetos, Propostas, Pagamentos e Históricos
        $clientIds = DB::table('clients')->where('user_id', $userId)->pluck('id');
        $projectIds = DB::table('projects')->whereIn('client_id', $clientIds)->pluck('id');
        
        DB::table('author_project')->whereIn('project_id', $projectIds)->delete();
        DB::table('project_histories')->whereIn('project_id', $projectIds)->delete();
        DB::table('project_attachments')->whereIn('project_id', $projectIds)->delete();
        
        DB::table('proposals')->whereIn('client_id', $clientIds)->delete();
        
        $paymentIds = DB::table('payments')->whereIn('project_id', $projectIds)->pluck('id');
        DB::table('payment_related_projects')->whereIn('payment_id', $paymentIds)->delete();
        DB::table('payments')->whereIn('project_id', $projectIds)->delete();
        
        DB::table('projects')->whereIn('client_id', $clientIds)->delete();
        echo "- Projetos, propostas e pagamentos removidos.\n";

        // 3. Clientes e Autores
        DB::table('clients')->where('user_id', $userId)->delete();
        DB::table('authors')->where('user_id', $userId)->delete();
        echo "- Clientes e Autores vinculados removidos.\n";

        // 4. Finanças (Transações, Contas Bancárias, Cartões de Crédito, Categorias)
        DB::table('transactions')->where('user_id', $userId)->delete();
        DB::table('transaction_categories')->where('user_id', $userId)->delete();
        DB::table('bank_accounts')->where('user_id', $userId)->delete();
        DB::table('credit_cards')->where('user_id', $userId)->delete();
        echo "- Transações financeiras, contas e cartões de crédito removidos.\n";

        // 5. Kanban (Colunas customizadas)
        DB::table('kanban_columns')->where('user_id', $userId)->delete();
        echo "- Colunas do Kanban removidas.\n";

        // 6. Lembretes e Portfólio
        DB::table('reminders')->where('user_id', $userId)->delete();
        DB::table('portfolio_items')->where('user_id', $userId)->delete();
        DB::table('portfolio_settings')->where('user_id', $userId)->delete();
        DB::table('portfolio_categories')->where('user_id', $userId)->delete();
        echo "- Lembretes e itens de portfólio removidos.\n";

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON;');
        } elseif ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
        }
    });

    echo "---------------------------------------------------------\n";
    echo "SUCESSO: Todos os dados de {$email} foram limpos!\n";
    echo "---------------------------------------------------------\n";
} catch (\Exception $e) {
    echo "Erro ao limpar banco de dados: " . $e->getMessage() . "\n";
    exit(1);
}
