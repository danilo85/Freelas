<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('editorial_revision_comments')) {
            Schema::create('editorial_revision_comments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('editorial_revision_correction_id')->constrained('editorial_revision_corrections')->onDelete('cascade');
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->string('author_name')->nullable();
                $table->text('message');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('editorial_revision_comments');
    }
};
