<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('project_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('action'); // criado, atualizado, aprovado, rejeitado
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('total_value', 10, 2)->nullable();
            $table->integer('initial_payment_percent')->nullable();
            $table->string('term')->nullable();
            $table->date('budget_date')->nullable();
            $table->date('expiration_date')->nullable();
            $table->text('additional_info')->nullable();
            $table->string('status');
            $table->timestamps();
        });

        // Seed initial history for existing projects
        $projects = DB::table('projects')->get();
        foreach ($projects as $project) {
            $client = DB::table('clients')->where('id', $project->client_id)->first();
            $userId = $client ? $client->user_id : null;

            DB::table('project_histories')->insert([
                'project_id' => $project->id,
                'user_id' => $userId,
                'action' => 'criado',
                'title' => $project->title,
                'description' => $project->description,
                'total_value' => $project->total_value,
                'initial_payment_percent' => $project->initial_payment_percent ?? 40,
                'term' => $project->term,
                'budget_date' => $project->budget_date,
                'expiration_date' => $project->expiration_date,
                'additional_info' => $project->additional_info,
                'status' => $project->status,
                'created_at' => $project->created_at ?? now(),
                'updated_at' => $project->updated_at ?? now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_histories');
    }
};
