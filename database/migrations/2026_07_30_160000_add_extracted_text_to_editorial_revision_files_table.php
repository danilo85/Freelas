<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('editorial_revision_files', 'extracted_text')) {
            Schema::table('editorial_revision_files', function (Blueprint $table) {
                $table->longText('extracted_text')->nullable()->after('file_type');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('editorial_revision_files', 'extracted_text')) {
            Schema::table('editorial_revision_files', function (Blueprint $table) {
                $table->dropColumn('extracted_text');
            });
        }
    }
};
