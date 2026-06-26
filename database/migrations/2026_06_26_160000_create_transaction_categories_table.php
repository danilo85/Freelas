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
        Schema::create('transaction_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('type'); // 'receita', 'despesa', 'ambos'
            $table->string('icon'); // Emojis ou nomes de ícones
            $table->timestamps();
        });

        // Seed das categorias padrão do sistema (onde user_id é nulo)
        $defaultCategories = [
            // Receitas
            ['name' => 'Freelance / Projetos', 'type' => 'receita', 'icon' => '💻'],
            ['name' => 'Salário / Pró-labore', 'type' => 'receita', 'icon' => '💼'],
            ['name' => 'Investimentos', 'type' => 'receita', 'icon' => '📈'],
            ['name' => 'Outras Receitas', 'type' => 'receita', 'icon' => '💵'],
            // Despesas
            ['name' => 'Alimentação', 'type' => 'despesa', 'icon' => '🍔'],
            ['name' => 'Transporte', 'type' => 'despesa', 'icon' => '🚗'],
            ['name' => 'Moradia & Contas', 'type' => 'despesa', 'icon' => '🏠'],
            ['name' => 'Lazer & Viagem', 'type' => 'despesa', 'icon' => '✈️'],
            ['name' => 'Saúde', 'type' => 'despesa', 'icon' => '❤️'],
            ['name' => 'Educação', 'type' => 'despesa', 'icon' => '📚'],
            ['name' => 'Assinaturas & Serviços', 'type' => 'despesa', 'icon' => '🔔'],
            ['name' => 'Marketing & Anúncios', 'type' => 'despesa', 'icon' => '📣'],
            ['name' => 'Impostos & Taxas', 'type' => 'despesa', 'icon' => '📄'],
            ['name' => 'Outras Despesas', 'type' => 'despesa', 'icon' => '💳'],
        ];

        $now = now();
        foreach ($defaultCategories as $cat) {
            DB::table('transaction_categories')->insert([
                'user_id' => null,
                'name' => $cat['name'],
                'type' => $cat['type'],
                'icon' => $cat['icon'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_categories');
    }
};
