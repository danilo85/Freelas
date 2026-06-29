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
        if (!Schema::hasTable('portfolio_settings')) {
            Schema::create('portfolio_settings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('site_title')->nullable();
                $table->string('site_subtitle')->nullable();
                $table->text('site_description')->nullable();
                $table->string('about_title')->nullable();
                $table->text('about_text')->nullable();
                $table->text('skills')->nullable();
                $table->string('contact_email')->nullable();
                $table->string('contact_phone')->nullable();
                $table->string('behance_url')->nullable();
                $table->text('faq_items')->nullable(); // JSON structure
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portfolio_settings');
    }
};
