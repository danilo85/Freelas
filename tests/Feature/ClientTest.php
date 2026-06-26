<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Client;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test guests are redirected to login.
     */
    public function test_guests_cannot_access_clients_routes(): void
    {
        $this->get(route('clients.index'))->assertRedirect(route('login'));
        $this->get(route('clients.create'))->assertRedirect(route('login'));
        $this->get(route('clients.show', 1))->assertRedirect(route('login'));
    }

    /**
     * Test user can only see their own clients in the listing.
     */
    public function test_user_can_only_see_their_own_clients(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $client1 = Client::factory()->create([
            'user_id' => $user1->id,
            'name' => 'Cliente do Usuario 1',
        ]);

        $client2 = Client::factory()->create([
            'user_id' => $user2->id,
            'name' => 'Cliente do Usuario 2',
        ]);

        $response = $this->actingAs($user1)->get(route('clients.index'));

        $response->assertStatus(200);
        $response->assertSee('Cliente do Usuario 1');
        $response->assertDontSee('Cliente do Usuario 2');
    }

    /**
     * Test user can view their own client's details and projects.
     */
    public function test_user_can_view_their_own_client_details(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create([
            'user_id' => $user->id,
            'name' => 'Acme Corporation',
            'email' => 'acme@corp.com',
        ]);

        $project1 = Project::factory()->create([
            'client_id' => $client->id,
            'title' => 'Projeto Incrivel',
            'status' => 'em andamento',
            'total_value' => 5000,
        ]);

        $project2 = Project::factory()->create([
            'client_id' => $client->id,
            'title' => 'Outro Projeto',
            'status' => 'concluido',
            'total_value' => 12500,
        ]);

        $response = $this->actingAs($user)->get(route('clients.show', $client));

        $response->assertStatus(200);
        $response->assertSee('Acme Corporation');
        $response->assertSee('acme@corp.com');
        $response->assertSee('Projeto Incrivel');
        $response->assertSee('Outro Projeto');
        $response->assertSee('R$ 5.000,00');
        $response->assertSee('R$ 12.500,00');
        $response->assertSee('R$ 17.500,00'); // Sum
    }

    /**
     * Test user can update client and marks registration_completed as true.
     */
    public function test_user_can_update_their_own_client(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create([
            'user_id' => $user->id,
            'name' => 'Cliente Antigo',
            'email' => 'antigo@exemplo.com',
            'registration_completed' => false,
        ]);

        $data = [
            'name' => 'Cliente Novo',
            'email' => 'novo@exemplo.com',
            'phone' => '(11) 99999-9999',
            'document' => '123.456.789-00',
        ];

        $response = $this->actingAs($user)->put(route('clients.update', $client), $data);
        $response->assertRedirect(route('clients.index'));

        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'name' => 'Cliente Novo',
            'email' => 'novo@exemplo.com',
            'registration_completed' => true,
        ]);
    }

    /**
     * Test user cannot access or modify other user's clients.
     */
    public function test_user_cannot_access_or_modify_other_users_client(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $clientOfUser2 = Client::factory()->create([
            'user_id' => $user2->id,
            'name' => 'Cliente de Outro',
        ]);

        // Tentar visualizar
        $this->actingAs($user1)->get(route('clients.show', $clientOfUser2))->assertStatus(403);

        // Tentar editar
        $this->actingAs($user1)->get(route('clients.edit', $clientOfUser2))->assertStatus(403);

        // Tentar atualizar
        $this->actingAs($user1)->put(route('clients.update', $clientOfUser2), [
            'name' => 'Invasor',
            'email' => 'invasor@exemplo.com',
        ])->assertStatus(403);

        // Tentar deletar
        $this->actingAs($user1)->delete(route('clients.destroy', $clientOfUser2))->assertStatus(403);
    }

    /**
     * Test guests can access public client payment statement with valid share token.
     */
    public function test_guest_can_access_public_client_statement(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create([
            'user_id' => $user->id,
            'name' => 'John Doe Public',
            'share_token' => 'custom-public-token-123',
        ]);

        $project = \App\Models\Project::create([
            'client_id' => $client->id,
            'title' => 'Approved Project',
            'status' => 'aprovado',
            'total_value' => 5000.00,
        ]);

        // A payment record
        \App\Models\Payment::create([
            'project_id' => $project->id,
            'amount' => 1500.00,
            'paid_at' => '2026-06-26',
            'payment_method' => 'Pix',
        ]);

        $response = $this->get(route('public.client.statement', 'custom-public-token-123'));

        $response->assertStatus(200);
        $response->assertSee('John Doe Public');
        $response->assertSee('Approved Project');
        $response->assertSee('R$ 5.000,00');
        $response->assertSee('R$ 1.500,00');
        $response->assertSee('R$ 3.500,00'); // Remaining balance
    }

    /**
     * Test public client statement returns 404 for invalid token.
     */
    public function test_public_client_statement_returns_404_for_invalid_token(): void
    {
        $this->get(route('public.client.statement', 'invalid-token-xyz'))
            ->assertStatus(404);
    }
}
