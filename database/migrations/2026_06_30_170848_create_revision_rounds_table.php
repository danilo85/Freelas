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
        Schema::create('revision_rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_revision_id')->constrained('project_revisions')->cascadeOnDelete();
            $table->integer('round_number');
            $table->text('description')->nullable();
            $table->string('status')->default('pendente'); // pendente, em_ajuste, aprovado
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('revision_rounds');
    }
};
