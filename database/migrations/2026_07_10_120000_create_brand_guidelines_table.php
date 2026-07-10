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
        Schema::create('brand_guidelines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->nullable()->constrained()->onDelete('set null');
            $table->string('brand_name');
            $table->string('logo_primary')->nullable();
            $table->string('logo_secondary')->nullable();
            $table->string('logo_symbol')->nullable();
            $table->text('logo_description')->nullable();
            $table->json('color_palette')->nullable();
            $table->json('typography')->nullable();
            $table->json('social_media')->nullable();
            $table->json('stationery')->nullable();
            $table->string('share_token')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brand_guidelines');
    }
};
