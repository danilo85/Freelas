<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('instagram_posts', function (Blueprint $table) {
            $table->json('media_urls')->nullable()->after('media_path');
            $table->boolean('has_logo_overlay')->default(false)->after('caption');
            $table->boolean('has_arrow_overlay')->default(false)->after('has_logo_overlay');
        });
    }

    public function down(): void
    {
        Schema::table('instagram_posts', function (Blueprint $table) {
            $table->dropColumn(['media_urls', 'has_logo_overlay', 'has_arrow_overlay']);
        });
    }
};
