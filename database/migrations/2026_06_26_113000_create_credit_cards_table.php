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
        Schema::create('credit_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('card_name');
            $table->string('bank_name');
            $table->decimal('limit', 12, 2)->default(0.00);
            $table->unsignedInteger('closing_day');
            $table->unsignedInteger('due_day');
            $table->string('flag'); // e.g. visa, mastercard, elo, amex, hipercard, outros
            $table->string('last_four_digits', 4)->nullable();
            $table->text('observations')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_cards');
    }
};
