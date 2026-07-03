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
        Schema::table('reminders', function (Blueprint $table) {
            $table->string('type')->default('text')->after('content');
            $table->json('items')->nullable()->after('type');
            $table->string('image_path')->nullable()->after('items');
            $table->integer('sort_order')->default(0)->after('image_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reminders', function (Blueprint $table) {
            $table->dropColumn(['type', 'items', 'image_path', 'sort_order']);
        });
    }
};
