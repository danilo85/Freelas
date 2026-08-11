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
        Schema::table('instagram_posts', function (Blueprint $table) {
            $table->string('category')->nullable()->after('media_type');
            $table->boolean('post_to_facebook')->default(false)->after('status');
            $table->string('facebook_post_id')->nullable()->after('instagram_media_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('instagram_posts', function (Blueprint $table) {
            $table->dropColumn(['category', 'post_to_facebook', 'facebook_post_id']);
        });
    }
};
