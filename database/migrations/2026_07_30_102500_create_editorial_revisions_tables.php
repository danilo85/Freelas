<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabela Principal de Revisões Editoriais
        Schema::create('editorial_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('revisor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('set null');
            $table->foreignId('project_id')->nullable()->constrained('projects')->onDelete('set null');
            
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('share_token')->unique();
            $table->string('status')->default('aguardando_revisor'); // aguardando_revisor, em_revisao, aguardando_autor, concluido
            $table->dateTime('deadline_at')->nullable();
            $table->string('password')->nullable();
            $table->string('storage_disk')->default('public');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Arquivos do Projeto de Revisão
        Schema::create('editorial_revision_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('editorial_revision_id')->constrained()->onDelete('cascade');
            $table->string('filename');
            $table->string('file_path');
            $table->bigInteger('file_size')->default(0);
            $table->string('mime_type')->nullable();
            $table->string('file_type')->default('word'); // word, pdf, image
            $table->integer('version')->default(1);
            $table->boolean('is_final')->default(false);
            $table->timestamps();
        });

        // 3. Apontamentos / Correções / Dúvidas de Revisão
        Schema::create('editorial_revision_corrections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('editorial_revision_id')->constrained()->onDelete('cascade');
            $table->foreignId('editorial_revision_file_id')->nullable()->constrained('editorial_revision_files')->onDelete('set null');
            $table->integer('page_number')->nullable();
            
            $table->text('original_text')->nullable();
            $table->text('suggested_text')->nullable();
            $table->text('justification')->nullable();
            
            $table->string('category')->default('ortografia'); // ortografia, gramatica, pontuacao, clareza, padronizacao, duvida, termo_tecnico, observacao
            $table->string('priority')->default('media'); // baixa, media, alta, urgente
            $table->string('status')->default('pendente'); // pendente, aceita, ignorada, respondida, resolvida
            $table->string('source')->default('revisor'); // revisor, autor, languagetool, ia
            
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        // 4. Mensagens e Diálogo entre Revisor e Autor
        Schema::create('editorial_revision_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('editorial_revision_correction_id')->constrained('editorial_revision_corrections')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('author_name')->nullable();
            $table->text('message');
            $table->timestamps();
        });

        // 5. Glossário do Projeto
        Schema::create('editorial_revision_glossaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('editorial_revision_id')->constrained()->onDelete('cascade');
            $table->string('correct_term');
            $table->text('incorrect_terms')->nullable(); // termos incorretos ou que devem ser evitados
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('editorial_revision_glossaries');
        Schema::dropIfExists('editorial_revision_comments');
        Schema::dropIfExists('editorial_revision_corrections');
        Schema::dropIfExists('editorial_revision_files');
        Schema::dropIfExists('editorial_revisions');
    }
};
