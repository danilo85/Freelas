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
        if (!Schema::hasTable('author_portfolio_item')) {
            Schema::create('author_portfolio_item', function (Blueprint $table) {
                $table->id();
                $table->foreignId('portfolio_item_id')->constrained()->onDelete('cascade');
                $table->foreignId('author_id')->constrained()->onDelete('cascade');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('author_portfolio_item');
    }
};
