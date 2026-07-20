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
        Schema::create('bible_highlights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('livro');
            $table->integer('capitulo');
            $table->integer('versiculo_inicial');
            $table->integer('versiculo_final')->nullable();
            $table->string('cor');
            $table->timestamps();
        });

        Schema::create('bible_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('livro');
            $table->integer('capitulo');
            $table->integer('versiculo_inicial');
            $table->integer('versiculo_final')->nullable();
            $table->text('conteudo');
            $table->timestamps();
        });

        Schema::create('bible_favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('livro');
            $table->integer('capitulo');
            $table->integer('versiculo_inicial');
            $table->integer('versiculo_final')->nullable();
            $table->timestamps();
        });

        Schema::create('bible_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('livro');
            $table->integer('capitulo');
            $table->integer('versiculo')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bible_progress');
        Schema::dropIfExists('bible_favorites');
        Schema::dropIfExists('bible_notes');
        Schema::dropIfExists('bible_highlights');
    }
};
