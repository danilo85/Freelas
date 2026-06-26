<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('share_token', 64)->nullable()->unique()->after('registration_completed');
        });

        // Generate tokens for existing clients
        $clients = \DB::table('clients')->get();
        foreach ($clients as $client) {
            \DB::table('clients')->where('id', $client->id)->update([
                'share_token' => bin2hex(random_bytes(16)),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('share_token');
        });
    }
};
