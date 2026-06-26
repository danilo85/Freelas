<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Client;
use App\Models\Project;
use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test guests are redirected from payments routes.
     */
    public function test_guests_cannot_access_payments_routes(): void
    {
        $this->get(route('payments.index'))->assertRedirect(route('login'));
        $this->get(route('payments.create'))->assertRedirect(route('login'));
        $this->post(route('payments.store'))->assertRedirect(route('login'));
    }

    /**
     * Test user can access payments index (calendar) and see stats.
     */
    public function test_user_can_access_payments_calendar(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $project = Project::factory()->create([
            'client_id' => $client->id,
            'status' => 'aprovado',
            'total_value' => 5000.00
        ]);

        $payment = Payment::create([
            'project_id' => $project->id,
            'amount' => 1500.00,
            'paid_at' => now()->toDateString(),
            'payment_method' => 'pix',
        ]);

        $response = $this->actingAs($user)->get(route('payments.index'));

        $response->assertStatus(200);
        $response->assertSee('Total do Mês');
        $response->assertSee('R$ 1.500,00');
    }

    /**
     * Test user can register payment, syncing transaction and updating project remaining balance.
     */
    public function test_user_can_register_payment_syncs_transaction(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $project = Project::factory()->create([
            'client_id' => $client->id,
            'status' => 'aprovado',
            'total_value' => 10000.00
        ]);

        $file = UploadedFile::fake()->create('nota_fiscal_ERP.pdf', 1000);

        // Register 4000 BRL payment
        $response = $this->actingAs($user)->post(route('payments.store'), [
            'project_id' => $project->id,
            'amount' => 'R$ 4.000,00',
            'paid_at' => now()->toDateString(),
            'payment_method' => 'pix',
            'bank_account' => 'Itaú PJ',
            'observations' => 'Primeira Parcela',
            'invoice' => $file,
        ]);

        $response->assertRedirect(route('payments.index'));
        
        // Assert Payment created in DB
        $this->assertDatabaseHas('payments', [
            'project_id' => $project->id,
            'amount' => 4000.00,
            'payment_method' => 'pix',
            'bank_account' => 'Itaú PJ',
        ]);

        $payment = Payment::where('project_id', $project->id)->first();
        $this->assertNotNull($payment->invoice_path);
        Storage::disk('local')->assertExists($payment->invoice_path);

        // Assert corresponding Transaction synced
        $this->assertDatabaseHas('transactions', [
            'project_id' => $project->id,
            'payment_id' => $payment->id,
            'type' => 'entrada',
            'amount' => 4000.00,
        ]);

        // Assert project remaining balance is now 6000 BRL
        $this->assertEquals(6000.00, $project->fresh()->remaining_balance);
    }

    /**
     * Test user cannot register payment on another user's project.
     */
    public function test_user_cannot_register_payment_on_others_project(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $client2 = Client::factory()->create(['user_id' => $user2->id]);
        $project2 = Project::factory()->create([
            'client_id' => $client2->id,
            'status' => 'aprovado',
            'total_value' => 5000.00
        ]);

        $response = $this->actingAs($user1)->post(route('payments.store'), [
            'project_id' => $project2->id,
            'amount' => 'R$ 1.500,00',
            'paid_at' => now()->toDateString(),
            'payment_method' => 'pix',
        ]);

        $response->assertStatus(404); // returns 404 because findOrFail on project subquery fails
    }

    /**
     * Test sharing invoice across multiple projects.
     */
    public function test_user_can_link_secondary_projects(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        
        $projectMain = Project::factory()->create([
            'client_id' => $client->id,
            'status' => 'aprovado',
            'total_value' => 10000.00,
        ]);
        
        $projectAdditional = Project::factory()->create([
            'client_id' => $client->id,
            'status' => 'aprovado',
            'total_value' => 5000.00,
        ]);

        $response = $this->actingAs($user)->post(route('payments.store'), [
            'project_id' => $projectMain->id,
            'amount' => 'R$ 5.000,00',
            'paid_at' => now()->toDateString(),
            'payment_method' => 'pix',
            'related_project_ids' => [$projectAdditional->id]
        ]);

        $response->assertRedirect(route('payments.index'));

        $payment = Payment::where('project_id', $projectMain->id)->first();
        $this->assertNotNull($payment);

        $this->assertDatabaseHas('payment_related_projects', [
            'payment_id' => $payment->id,
            'project_id' => $projectAdditional->id,
        ]);
    }

    /**
     * Test secure invoice download and deletion cascade.
     */
    public function test_payment_download_invoice_and_deletion_flow(): void
    {
        Storage::fake('local');

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $client1 = Client::factory()->create(['user_id' => $user1->id]);
        $project1 = Project::factory()->create([
            'client_id' => $client1->id,
            'status' => 'aprovado',
        ]);

        $fakePath = 'invoices/test_invoice.pdf';
        Storage::disk('local')->put($fakePath, 'invoice pdf contents');

        $payment = Payment::create([
            'project_id' => $project1->id,
            'amount' => 3000.00,
            'paid_at' => now()->toDateString(),
            'payment_method' => 'pix',
            'invoice_path' => $fakePath,
        ]);

        // User 2 (unauthorized) download attempt -> 403
        $this->actingAs($user2)->get(route('payments.download-invoice', $payment->id))->assertStatus(403);

        // User 1 (owner) download attempt -> 200
        $response = $this->actingAs($user1)->get(route('payments.download-invoice', $payment->id));
        $response->assertStatus(200);
        $this->assertEquals('invoice pdf contents', $response->streamedContent());

        // Deletion by owner
        $this->actingAs($user1)->delete(route('payments.destroy', $payment->id))->assertRedirect(route('payments.index'));

        // Assert payment and transaction are deleted
        $this->assertDatabaseMissing('payments', ['id' => $payment->id]);
        $this->assertDatabaseMissing('transactions', ['payment_id' => $payment->id]);
        Storage::disk('local')->assertMissing($fakePath);
    }

    /**
     * Test project status cannot be changed back to 'analisando' if there are payments registered.
     */
    public function test_cannot_change_status_to_analisando_if_has_payments(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $project = Project::factory()->create([
            'client_id' => $client->id,
            'status' => 'aprovado',
            'total_value' => 5000.00,
        ]);

        $payment = Payment::create([
            'project_id' => $project->id,
            'amount' => 1000.00,
            'paid_at' => now()->toDateString(),
            'payment_method' => 'pix',
        ]);

        // Attempting to change status to 'analisando' via API should return 422
        $response = $this->actingAs($user)
            ->patchJson(route('projects.update-status', $project->id), ['status' => 'analisando']);
        $response->assertStatus(422);

        // Attempting to change status to 'analisando' via update form should return error redirect
        $response = $this->actingAs($user)
            ->put(route('projects.update', $project->id), [
                'title' => $project->title,
                'description' => $project->description,
                'client_id' => $client->id,
                'total_value' => 'R$ 5.000,00',
                'initial_payment_percent' => 50,
                'term' => '30 dias',
                'budget_date' => now()->toDateString(),
                'expiration_date' => now()->addDays(10)->toDateString(),
                'status' => 'analisando',
            ]);
        $response->assertSessionHasErrors('status');
    }

    /**
     * Test first payment registration changes project status from rascunho/analisando to 'aprovado'.
     */
    public function test_payment_registration_updates_status_to_aprovado(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $project = Project::factory()->create([
            'client_id' => $client->id,
            'status' => 'analisando',
            'total_value' => 5000.00,
        ]);

        $response = $this->actingAs($user)->post(route('payments.store'), [
            'project_id' => $project->id,
            'amount' => 'R$ 1.000,00',
            'paid_at' => now()->toDateString(),
            'payment_method' => 'pix',
        ]);

        $response->assertRedirect(route('payments.index'));
        $this->assertEquals('aprovado', $project->fresh()->status);
    }

    /**
     * Test registering full payment updates status to 'quitado' and blocks further payments.
     */
    public function test_payment_registration_updates_status_to_quitado_and_blocks_further_payments(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $project = Project::factory()->create([
            'client_id' => $client->id,
            'status' => 'aprovado',
            'total_value' => 5000.00,
        ]);

        // Register payment that fully pays the budget
        $response = $this->actingAs($user)->post(route('payments.store'), [
            'project_id' => $project->id,
            'amount' => 'R$ 5.000,00',
            'paid_at' => now()->toDateString(),
            'payment_method' => 'pix',
        ]);

        $response->assertRedirect(route('payments.index'));
        $this->assertEquals('quitado', $project->fresh()->status);

        // Attempting to register another payment should be blocked
        $response = $this->actingAs($user)->post(route('payments.store'), [
            'project_id' => $project->id,
            'amount' => 'R$ 1.000,00',
            'paid_at' => now()->toDateString(),
            'payment_method' => 'pix',
        ]);
        $response->assertSessionHasErrors('amount');
    }

    /**
     * Test payment registration is blocked if status is 'rejeitado'.
     */
    public function test_cannot_register_payment_if_status_is_rejeitado(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $project = Project::factory()->create([
            'client_id' => $client->id,
            'status' => 'rejeitado',
            'total_value' => 5000.00,
        ]);

        $response = $this->actingAs($user)->post(route('payments.store'), [
            'project_id' => $project->id,
            'amount' => 'R$ 1.000,00',
            'paid_at' => now()->toDateString(),
            'payment_method' => 'pix',
        ]);

        $response->assertSessionHasErrors('amount');
    }

    /**
     * Test that deleting a payment on a 'quitado' project reverts its status back to 'aprovado' if there's remaining balance.
     */
    public function test_deleting_payment_on_quitado_project_reverts_status_to_aprovado(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $project = Project::factory()->create([
            'client_id' => $client->id,
            'status' => 'aprovado',
            'total_value' => 5000.00,
        ]);

        // Register full payment, making status 'quitado'
        $payment = Payment::create([
            'project_id' => $project->id,
            'amount' => 5000.00,
            'paid_at' => now()->toDateString(),
            'payment_method' => 'pix',
        ]);
        $project->update(['status' => 'quitado']);

        $this->assertEquals('quitado', $project->fresh()->status);

        // Delete payment
        $response = $this->actingAs($user)->delete(route('payments.destroy', $payment->id));

        $response->assertRedirect(route('payments.index'));
        $this->assertEquals('aprovado', $project->fresh()->status);
    }
}
