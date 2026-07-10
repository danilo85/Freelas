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
        Schema::table('brand_guidelines', function (Blueprint $table) {
            $table->string('final_package')->nullable()->after('is_active');
            $table->text('logo_horizontal_desc')->nullable()->after('logo_description');
            $table->text('logo_vertical_desc')->nullable()->after('logo_horizontal_desc');
            $table->text('logo_symbol_desc')->nullable()->after('logo_vertical_desc');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('brand_guidelines', function (Blueprint $table) {
            $table->dropColumn(['final_package', 'logo_horizontal_desc', 'logo_vertical_desc', 'logo_symbol_desc']);
        });
    }
};
