<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('file_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('share_token')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('expires_at');
            $table->integer('download_limit')->nullable();
            $table->integer('download_count')->default(0);
            $table->integer('view_count')->default(0);
            $table->string('password')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('file_share_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_share_id')->constrained()->onDelete('cascade');
            $table->string('filename');
            $table->string('file_path');
            $table->bigInteger('file_size');
            $table->string('mime_type')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('file_share_items');
        Schema::dropIfExists('file_shares');
    }
};
