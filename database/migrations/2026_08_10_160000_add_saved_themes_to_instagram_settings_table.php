<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('instagram_settings', function (Blueprint $table) {
            $table->json('saved_themes')->nullable()->after('arrow_path');
        });
    }

    public function down(): void
    {
        Schema::table('instagram_settings', function (Blueprint $table) {
            $table->dropColumn('saved_themes');
        });
    }
};
