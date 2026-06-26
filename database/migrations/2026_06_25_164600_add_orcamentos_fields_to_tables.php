<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Alterar a tabela 'projects' para adicionar campos do orçamento
        Schema::table('projects', function (Blueprint $table) {
            $table->integer('initial_payment_percent')->default(40);
            $table->string('term')->nullable(); // Prazo do projeto
            $table->date('budget_date')->nullable(); // Data do orçamento
            $table->date('expiration_date')->nullable(); // Data de validade
            $table->text('additional_info')->nullable(); // Informações adicionais
        });

        // 2. Alterar a tabela 'clients' para incluir flag de cadastro completo
        Schema::table('clients', function (Blueprint $table) {
            $table->boolean('registration_completed')->default(true);
        });

        // 3. Alterar a tabela 'authors' para incluir flag de cadastro completo
        Schema::table('authors', function (Blueprint $table) {
            $table->boolean('registration_completed')->default(true);
        });

        // 4. Criar a tabela pivô 'author_project' para relacionamento muitos-para-muitos
        Schema::create('author_project', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->constrained('authors')->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('author_project');

        Schema::table('authors', function (Blueprint $table) {
            $table->dropColumn('registration_completed');
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('registration_completed');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'initial_payment_percent',
                'term',
                'budget_date',
                'expiration_date',
                'additional_info'
            ]);
        });
    }
};
