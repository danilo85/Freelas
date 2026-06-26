<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\BankAccount;
use App\Models\Client;
use App\Models\Project;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BankAccountTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test guests are redirected from bank account routes.
     */
    public function test_guests_cannot_access_bank_account_routes(): void
    {
        $this->get(route('bank-accounts.index'))->assertRedirect(route('login'));
        $this->get(route('bank-accounts.create'))->assertRedirect(route('login'));
        $this->post(route('bank-accounts.store'))->assertRedirect(route('login'));
    }

    /**
     * Test a user can create a bank account.
     */
    public function test_user_can_create_bank_account(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('bank-accounts.store'), [
            'bank_name' => 'Nubank',
            'account_name' => 'Conta PJ Freelance',
            'account_type' => 'digital',
            'person_type' => 'PJ',
            'agency' => '0001',
            'account_number' => '123456-7',
            'initial_balance' => 'R$ 1.500,00',
            'observations' => 'Conta para receber pagamentos de sites',
        ]);

        $response->assertRedirect(route('bank-accounts.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('bank_accounts', [
            'user_id' => $user->id,
            'bank_name' => 'Nubank',
            'account_name' => 'Conta PJ Freelance',
            'account_type' => 'digital',
            'person_type' => 'PJ',
            'agency' => '0001',
            'account_number' => '123456-7',
            'initial_balance' => 1500.00,
        ]);
    }

    /**
     * Test a user can create a custom/other bank account.
     */
    public function test_user_can_create_custom_bank_account(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('bank-accounts.store'), [
            'bank_name' => 'Outro',
            'custom_bank_name' => 'Cooperativa Viacredi',
            'account_name' => 'Conta Viacredi',
            'account_type' => 'corrente',
            'person_type' => 'PF',
            'agency' => '1020',
            'account_number' => '98765-4',
            'initial_balance' => '500,00',
        ]);

        $response->assertRedirect(route('bank-accounts.index'));

        $this->assertDatabaseHas('bank_accounts', [
            'user_id' => $user->id,
            'bank_name' => 'Cooperativa Viacredi',
            'account_name' => 'Conta Viacredi',
            'initial_balance' => 500.00,
        ]);
    }

    /**
     * Test a user can list only their own bank accounts.
     */
    public function test_user_can_only_list_their_own_bank_accounts(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $account1 = BankAccount::create([
            'user_id' => $user1->id,
            'bank_name' => 'Nubank',
            'account_name' => 'User 1 Account',
            'account_type' => 'digital',
            'person_type' => 'PJ',
            'initial_balance' => 100.00,
        ]);

        $account2 = BankAccount::create([
            'user_id' => $user2->id,
            'bank_name' => 'Itaú',
            'account_name' => 'User 2 Account',
            'account_type' => 'corrente',
            'person_type' => 'PF',
            'initial_balance' => 200.00,
        ]);

        $response = $this->actingAs($user1)->get(route('bank-accounts.index'));

        $response->assertStatus(200);
        $response->assertSee('User 1 Account');
        $response->assertDontSee('User 2 Account');
    }

    /**
     * Test tenancy security checks on edit/update/destroy.
     */
    public function test_user_cannot_modify_others_bank_accounts(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $account = BankAccount::create([
            'user_id' => $user2->id,
            'bank_name' => 'Itaú',
            'account_name' => 'User 2 Account',
            'account_type' => 'corrente',
            'person_type' => 'PF',
            'initial_balance' => 200.00,
        ]);

        // Attempt edit
        $this->actingAs($user1)
            ->get(route('bank-accounts.edit', $account->id))
            ->assertStatus(403);

        // Attempt update
        $this->actingAs($user1)
            ->put(route('bank-accounts.update', $account->id), [
                'bank_name' => 'Nubank',
                'account_name' => 'Hacked Name',
                'account_type' => 'digital',
                'person_type' => 'PJ',
                'initial_balance' => '1.00',
            ])
            ->assertStatus(403);

        // Attempt delete
        $this->actingAs($user1)
            ->delete(route('bank-accounts.destroy', $account->id))
            ->assertStatus(403);
    }

    /**
     * Test user can update their bank account.
     */
    public function test_user_can_update_their_bank_account(): void
    {
        $user = User::factory()->create();
        $account = BankAccount::create([
            'user_id' => $user->id,
            'bank_name' => 'Nubank',
            'account_name' => 'Old Account Name',
            'account_type' => 'digital',
            'person_type' => 'PJ',
            'initial_balance' => 100.00,
        ]);

        $response = $this->actingAs($user)->put(route('bank-accounts.update', $account->id), [
            'bank_name' => 'Itaú',
            'account_name' => 'New Account Name',
            'account_type' => 'corrente',
            'person_type' => 'PF',
            'agency' => '9999',
            'account_number' => '88888-8',
            'initial_balance' => 'R$ 2.500,50',
            'observations' => 'Updated observations',
        ]);

        $response->assertRedirect(route('bank-accounts.index'));
        
        $this->assertDatabaseHas('bank_accounts', [
            'id' => $account->id,
            'bank_name' => 'Itaú',
            'account_name' => 'New Account Name',
            'account_type' => 'corrente',
            'person_type' => 'PF',
            'agency' => '9999',
            'account_number' => '88888-8',
            'initial_balance' => 2500.50,
            'observations' => 'Updated observations',
        ]);
    }

    /**
     * Test user can delete their bank account.
     */
    public function test_user_can_delete_their_bank_account(): void
    {
        $user = User::factory()->create();
        $account = BankAccount::create([
            'user_id' => $user->id,
            'bank_name' => 'Nubank',
            'account_name' => 'Account to Delete',
            'account_type' => 'digital',
            'person_type' => 'PJ',
            'initial_balance' => 100.00,
        ]);

        $response = $this->actingAs($user)->delete(route('bank-accounts.destroy', $account->id));

        $response->assertRedirect(route('bank-accounts.index'));
        $this->assertDatabaseMissing('bank_accounts', ['id' => $account->id]);
    }

    /**
     * Test registering a payment with a bank account ID.
     */
    public function test_registering_payment_with_bank_account(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $project = Project::factory()->create([
            'client_id' => $client->id,
            'status' => 'aprovado',
            'total_value' => 10000.00
        ]);

        $account = BankAccount::create([
            'user_id' => $user->id,
            'bank_name' => 'Nubank',
            'account_name' => 'Nu PJ',
            'account_type' => 'digital',
            'person_type' => 'PJ',
            'initial_balance' => 500.00,
        ]);

        // Post a payment of R$ 1.500,00 linked to this bank account
        $response = $this->actingAs($user)->post(route('payments.store'), [
            'project_id' => $project->id,
            'amount' => 'R$ 1.500,00',
            'paid_at' => now()->toDateString(),
            'payment_method' => 'pix',
            'bank_account_id' => $account->id,
            'observations' => 'Associação com conta bancária',
        ]);

        $response->assertRedirect(route('payments.index'));

        // Assert payment is created with the relation ID and textual fallback
        $this->assertDatabaseHas('payments', [
            'project_id' => $project->id,
            'amount' => 1500.00,
            'bank_account_id' => $account->id,
            'bank_account' => 'Nubank (Nu PJ)',
        ]);

        // Verify index view correctly consolidates the balance (500 initial + 1500 payment = 2000 total)
        $responseIndex = $this->actingAs($user)->get(route('bank-accounts.index'));
        $responseIndex->assertStatus(200);
        $responseIndex->assertSee('R$ 2.000,00'); // Combined balance or individual account current balance
    }
}
