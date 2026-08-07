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
        if (Schema::hasTable('portfolio_items') && !Schema::hasColumn('portfolio_items', 'gallery_spacing')) {
            Schema::table('portfolio_items', function (Blueprint $table) {
                $table->integer('gallery_spacing')->default(0)->after('thumb_path');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('portfolio_items') && Schema::hasColumn('portfolio_items', 'gallery_spacing')) {
            Schema::table('portfolio_items', function (Blueprint $table) {
                $table->dropColumn('gallery_spacing');
            });
        }
    }
};
