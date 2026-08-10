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
        Schema::create('instagram_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('instagram_account_id')->nullable()->constrained('instagram_accounts')->onDelete('set null');
            $table->string('media_type')->default('IMAGE'); // IMAGE, REELS, CAROUSEL
            $table->text('media_path');
            $table->text('caption')->nullable();
            $table->enum('status', ['rascunho', 'agendado', 'publicado', 'erro'])->default('rascunho');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->string('instagram_media_id')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instagram_posts');
    }
};
