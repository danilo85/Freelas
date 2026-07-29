<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('file_shares', function (Blueprint $table) {
            if (!Schema::hasColumn('file_shares', 'storage_disk')) {
                $table->string('storage_disk')->default('public')->after('is_hidden');
            }
        });
    }

    public function down(): void
    {
        Schema::table('file_shares', function (Blueprint $table) {
            if (Schema::hasColumn('file_shares', 'storage_disk')) {
                $table->dropColumn('storage_disk');
            }
        });
    }
};
