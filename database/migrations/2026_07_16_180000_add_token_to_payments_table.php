<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('token', 64)->nullable()->unique()->after('id');
        });

        // Generate token for existing payments
        $payments = DB::table('payments')->get();
        foreach ($payments as $payment) {
            DB::table('payments')
                ->where('id', $payment->id)
                ->update(['token' => Str::random(32)]);
        }
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('token');
        });
    }
};
