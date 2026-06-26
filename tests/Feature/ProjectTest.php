<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Client;
use App\Models\Author;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test guests are redirected from projects routes.
     */
    public function test_guests_cannot_access_projects_routes(): void
    {
        $this->get(route('projects.index'))->assertRedirect(route('login'));
        $this->get(route('projects.create'))->assertRedirect(route('login'));
        $this->post(route('projects.store'))->assertRedirect(route('login'));
    }

    /**
     * Test user can only see projects of their own clients.
     */
    public function test_user_can_only_see_their_own_projects(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $client1 = Client::factory()->create(['user_id' => $user1->id]);
        $client2 = Client::factory()->create(['user_id' => $user2->id]);

        $project1 = Project::factory()->create([
            'client_id' => $client1->id,
            'title' => 'Projeto do User 1',
        ]);

        $project2 = Project::factory()->create([
            'client_id' => $client2->id,
            'title' => 'Projeto do User 2',
        ]);

        $response = $this->actingAs($user1)->get(route('projects.index'));

        $response->assertStatus(200);
        $response->assertSee('Projeto do User 1');
        $response->assertDontSee('Projeto do User 2');
    }

    /**
     * Test creating a project with existing client and authors.
     */
    public function test_user_can_create_project_with_existing_relations(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $author = Author::factory()->create(['user_id' => $user->id]);

        $data = [
            'title' => 'Sistema ERP',
            'description' => 'Desenvolvimento de ERP completo.',
            'client_id' => $client->id,
            'total_value' => 'R$ 15.000,00',
            'initial_payment_percent' => 50,
            'term' => '90 dias',
            'budget_date' => '2026-06-25',
            'expiration_date' => '2026-07-05',
            'status' => 'analisando',
            'author_ids' => [$author->id],
            'additional_info' => 'Observações importantes.',
        ];

        $response = $this->actingAs($user)->post(route('projects.store'), $data);

        $response->assertRedirect(route('projects.index'));
        $this->assertDatabaseHas('projects', [
            'title' => 'Sistema ERP',
            'client_id' => $client->id,
            'total_value' => 15000.00,
            'initial_payment_percent' => 50,
            'status' => 'analisando',
            'term' => '90 dias',
        ]);

        $project = Project::where('title', 'Sistema ERP')->first();
        $this->assertTrue($project->authors->contains($author->id));
    }

    /**
     * Test creating a project with client and author registered on-the-fly.
     */
    public function test_user_can_create_project_registering_client_and_author_on_the_fly(): void
    {
        $user = User::factory()->create();

        $data = [
            'title' => 'Landing Page Rápida',
            'description' => 'Criar LP institucional.',
            'new_client_name' => 'Novo Cliente S/A',
            'total_value' => 'R$ 1.250,50',
            'initial_payment_percent' => 40,
            'term' => '10 dias',
            'budget_date' => '2026-06-25',
            'expiration_date' => '2026-07-05',
            'status' => 'rascunho',
            'new_author_names' => ['Novo Autor On The Fly'],
        ];

        $response = $this->actingAs($user)->post(route('projects.store'), $data);
        $response->assertRedirect(route('projects.index'));

        // Verifica criação do cliente temporário (registration_completed = false)
        $this->assertDatabaseHas('clients', [
            'user_id' => $user->id,
            'name' => 'Novo Cliente S/A',
            'registration_completed' => false,
        ]);

        // Verifica criação do autor temporário (registration_completed = false)
        $this->assertDatabaseHas('authors', [
            'user_id' => $user->id,
            'name' => 'Novo Autor On The Fly',
            'registration_completed' => false,
        ]);

        $client = Client::where('name', 'Novo Cliente S/A')->first();
        $author = Author::where('name', 'Novo Autor On The Fly')->first();

        // Verifica projeto associado a eles
        $this->assertDatabaseHas('projects', [
            'title' => 'Landing Page Rápida',
            'client_id' => $client->id,
            'total_value' => 1250.50,
            'status' => 'rascunho',
        ]);

        $project = Project::where('title', 'Landing Page Rápida')->first();
        $this->assertTrue($project->authors->contains($author->id));
    }

    /**
     * Test tenancy protection for viewing and editing projects.
     */
    public function test_user_cannot_access_or_modify_other_users_projects(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $clientOfUser2 = Client::factory()->create(['user_id' => $user2->id]);
        $projectOfUser2 = Project::factory()->create([
            'client_id' => $clientOfUser2->id,
            'title' => 'Projeto Invisivel',
        ]);

        // Tentar visualizar
        $this->actingAs($user1)->get(route('projects.show', $projectOfUser2))->assertStatus(403);

        // Tentar editar
        $this->actingAs($user1)->get(route('projects.edit', $projectOfUser2))->assertStatus(403);

        // Tentar atualizar
        $this->actingAs($user1)->put(route('projects.update', $projectOfUser2), [
            'title' => 'Hackeado',
            'description' => 'Tentativa',
            'client_id' => $clientOfUser2->id,
            'total_value' => 'R$ 1.000,00',
            'initial_payment_percent' => 40,
            'term' => '1 dia',
            'budget_date' => '2026-06-25',
            'expiration_date' => '2026-07-05',
            'status' => 'rascunho',
        ])->assertStatus(403);

        // Tentar deletar
        $this->actingAs($user1)->delete(route('projects.destroy', $projectOfUser2))->assertStatus(403);
    }

    /**
     * Test history records are logged and can be previewed.
     */
    public function test_project_history_is_recorded_and_can_be_previewed(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);

        $project = Project::create([
            'title' => 'ERP Versão 1',
            'description' => 'ERP Completo V1',
            'client_id' => $client->id,
            'total_value' => 10000.00,
            'initial_payment_percent' => 50,
            'term' => '30 dias',
            'budget_date' => '2026-06-25',
            'expiration_date' => '2026-07-05',
            'status' => 'rascunho',
        ]);

        // Verifies history is logged on created event
        $this->assertDatabaseHas('project_histories', [
            'project_id' => $project->id,
            'title' => 'ERP Versão 1',
            'action' => 'criado',
        ]);

        // Updates project
        $project->update([
            'title' => 'ERP Versão 2',
            'total_value' => 12000.00,
        ]);

        // Verifies history is logged on updated event
        $this->assertDatabaseHas('project_histories', [
            'project_id' => $project->id,
            'title' => 'ERP Versão 2',
            'action' => 'atualizado',
        ]);

        $version1 = $project->histories()->where('title', 'ERP Versão 1')->first();

        // Acting as user, visit show page with version_id of version 1
        $response = $this->actingAs($user)->get(route('projects.show', [$project->id, 'version_id' => $version1->id]));

        $response->assertStatus(200);
        $response->assertSee('ERP Versão 1');
        $response->assertSee('Você está visualizando uma versão anterior');
    }

    /**
     * Test project status update API.
     */
    public function test_user_can_update_project_status_via_api(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $client1 = Client::factory()->create(['user_id' => $user1->id]);
        $client2 = Client::factory()->create(['user_id' => $user2->id]);

        $project1 = Project::factory()->create([
            'client_id' => $client1->id,
            'status' => 'rascunho',
        ]);

        $project2 = Project::factory()->create([
            'client_id' => $client2->id,
            'status' => 'rascunho',
        ]);

        // Guest cannot update status
        $this->patchJson(route('projects.update-status', $project1->id), ['status' => 'aprovado'])
            ->assertStatus(401);

        // User1 can update project1 status
        $response = $this->actingAs($user1)
            ->patchJson(route('projects.update-status', $project1->id), ['status' => 'aprovado']);
        
        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'status' => 'aprovado']);
        $this->assertEquals('aprovado', $project1->fresh()->status);

        // User1 cannot update project2 status
        $this->actingAs($user1)
            ->patchJson(route('projects.update-status', $project2->id), ['status' => 'aprovado'])
            ->assertStatus(403);

        // Invalid status values fail validation
        $this->actingAs($user1)
            ->patchJson(route('projects.update-status', $project1->id), ['status' => 'invalid_status_value'])
            ->assertStatus(422);
    }
}
