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
        Schema::create('revision_annotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('revision_file_id')->constrained('revision_files')->cascadeOnDelete();
            $table->integer('page_number')->default(1);
            $table->longText('drawing_data')->nullable(); // JSON drawing paths
            $table->text('comment');
            $table->string('status')->default('aberto'); // aberto, resolvido
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('revision_annotations');
    }
};
