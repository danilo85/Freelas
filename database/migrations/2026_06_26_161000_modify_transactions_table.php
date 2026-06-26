<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->change();
            $table->date('paid_at')->nullable()->change();
            $table->string('description')->nullable()->after('project_id');
            $table->foreignId('category_id')->nullable()->after('description')->constrained('transaction_categories')->nullOnDelete();
            $table->foreignId('bank_account_id')->nullable()->after('category_id')->constrained('bank_accounts')->nullOnDelete();
            $table->foreignId('credit_card_id')->nullable()->after('bank_account_id')->constrained('credit_cards')->nullOnDelete();
            $table->date('due_date')->nullable()->after('amount');
            $table->string('status')->default('pago')->after('due_date'); // 'pendente', 'pago'
            $table->string('attachment_path')->nullable()->after('status');
            $table->string('classification')->default('PJ')->after('attachment_path'); // 'PF', 'PJ'
            $table->string('group_code')->nullable()->after('classification'); // para agrupar parcelas/recorrências
            $table->integer('installment_number')->nullable()->after('group_code');
            $table->integer('total_installments')->nullable()->after('installment_number');
            $table->string('recurrence')->nullable()->after('total_installments'); // 'diaria', 'semanal', 'mensal', 'anual'
        });

        // Lógica de Backfill para registros existentes criados a partir de pagamentos
        $payments = DB::table('payments')->get();
        $defaultUser = DB::table('users')->first();
        $defaultCategoryId = DB::table('transaction_categories')->where('name', 'Freelance / Projetos')->value('id');

        foreach ($payments as $payment) {
            $project = DB::table('projects')->where('id', $payment->project_id)->first();
            $client = $project ? DB::table('clients')->where('id', $project->client_id)->first() : null;
            $userId = $client ? $client->user_id : ($defaultUser ? $defaultUser->id : null);

            DB::table('transactions')
                ->where('payment_id', $payment->id)
                ->update([
                    'user_id' => $userId,
                    'description' => $project ? 'Recebimento: ' . $project->title : 'Recebimento de Projeto',
                    'category_id' => $defaultCategoryId,
                    'bank_account_id' => $payment->bank_account_id,
                    'due_date' => $payment->paid_at,
                    'status' => 'pago',
                    'classification' => 'PJ',
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['category_id']);
            $table->dropForeign(['bank_account_id']);
            $table->dropForeign(['credit_card_id']);

            $table->dropColumn([
                'user_id',
                'description',
                'category_id',
                'bank_account_id',
                'credit_card_id',
                'due_date',
                'status',
                'attachment_path',
                'classification',
                'group_code',
                'installment_number',
                'total_installments',
                'recurrence',
            ]);
        });
    }
};
