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
        Schema::create('revision_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('revision_round_id')->constrained('revision_rounds')->cascadeOnDelete();
            $table->string('folder_name')->nullable();
            $table->string('filename');
            $table->string('file_path');
            $table->string('file_type');
            $table->unsignedBigInteger('file_size');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('revision_files');
    }
};
