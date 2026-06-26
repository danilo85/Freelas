<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Transaction;
use App\Models\TransactionCategory;
use App\Models\BankAccount;
use App\Models\CreditCard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;
use Illuminate\Support\Str;

class FinanceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test guests are redirected from finances routes.
     */
    public function test_guests_cannot_access_finances_routes(): void
    {
        $this->get(route('finances.index'))->assertRedirect(route('login'));
        $this->get(route('finances.create'))->assertRedirect(route('login'));
        $this->post(route('finances.store'))->assertRedirect(route('login'));
    }

    /**
     * Test creating a single transaction.
     */
    public function test_user_can_create_single_transaction(): void
    {
        $user = User::factory()->create();
        $account = BankAccount::create([
            'user_id' => $user->id,
            'bank_name' => 'Nubank',
            'account_name' => 'Conta PJ',
            'account_type' => 'digital',
            'person_type' => 'PJ',
            'initial_balance' => 1000.00
        ]);

        $category = TransactionCategory::create([
            'user_id' => $user->id,
            'name' => 'Serviços',
            'type' => 'despesa',
            'icon' => '🔧'
        ]);

        $response = $this->actingAs($user)->post(route('finances.store'), [
            'type' => 'saida',
            'description' => 'Servidor DigitalOcean',
            'amount' => 'R$ 150,00',
            'due_date' => Carbon::now()->toDateString(),
            'status' => 'pago',
            'classification' => 'PJ',
            'category_id' => $category->id,
            'bank_account_id' => $account->id,
            'repeat_type' => 'single',
        ]);

        $response->assertRedirect(route('finances.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'description' => 'Servidor DigitalOcean',
            'amount' => 150.00,
            'status' => 'pago',
            'classification' => 'PJ',
            'bank_account_id' => $account->id,
        ]);
    }

    /**
     * Test creating an installment transaction by dividing total amount.
     */
    public function test_user_can_create_installments_dividing_total(): void
    {
        $user = User::factory()->create();
        $category = TransactionCategory::where('name', 'Outras Despesas')->first();

        $response = $this->actingAs($user)->post(route('finances.store'), [
            'type' => 'saida',
            'description' => 'Compra Notebook',
            'amount' => '3.000,00',
            'due_date' => '2026-06-26',
            'status' => 'pago',
            'classification' => 'PJ',
            'category_id' => $category->id,
            'repeat_type' => 'installments',
            'installment_mode' => 'total',
            'installments_count' => 3,
        ]);

        $response->assertRedirect(route('finances.index'));

        // Should create 3 transactions
        $this->assertDatabaseCount('transactions', 3);

        // Check first installment (status pago as requested)
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'description' => 'Compra Notebook (1/3)',
            'amount' => 1000.00,
            'due_date' => '2026-06-26 00:00:00',
            'status' => 'pago',
        ]);

        // Check second installment (future, status pendente)
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'description' => 'Compra Notebook (2/3)',
            'amount' => 1000.00,
            'due_date' => '2026-07-26 00:00:00',
            'status' => 'pendente',
        ]);
    }

    /**
     * Test creating recurring transactions pre-populating 12 occurrences.
     */
    public function test_user_can_create_recurring_transactions(): void
    {
        $user = User::factory()->create();
        $category = TransactionCategory::where('name', 'Assinaturas & Serviços')->first();

        $response = $this->actingAs($user)->post(route('finances.store'), [
            'type' => 'saida',
            'description' => 'Assinatura Spotify',
            'amount' => '34,90',
            'due_date' => '2026-06-26',
            'status' => 'pago',
            'classification' => 'PF',
            'category_id' => $category->id,
            'repeat_type' => 'recurring',
            'recurrence_period' => 'mensal',
        ]);

        $response->assertRedirect(route('finances.index'));

        // Should create exactly 12 transactions
        $this->assertDatabaseCount('transactions', 12);

        // Verify the 1st one is paid and the rest are pending
        $this->assertEquals(1, Transaction::where('status', 'pago')->count());
        $this->assertEquals(11, Transaction::where('status', 'pendente')->count());
    }

    /**
     * Test tenancy isolation on show/edit/update/delete.
     */
    public function test_user_cannot_access_others_transactions(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $category = TransactionCategory::where('name', 'Outras Despesas')->first();
        $t = Transaction::create([
            'user_id' => $user2->id,
            'description' => 'User 2 Secret Expense',
            'amount' => 100.00,
            'due_date' => '2026-06-26',
            'status' => 'pendente',
            'type' => 'saida',
            'category_id' => $category->id,
        ]);

        // Attempt edit
        $this->actingAs($user1)->get(route('finances.edit', $t->id))->assertStatus(403);

        // Attempt update
        $this->actingAs($user1)->put(route('finances.update', $t->id), [
            'description' => 'Hacked',
            'amount' => '1.00',
            'due_date' => '2026-06-26',
            'status' => 'pago',
            'classification' => 'PF',
            'category_id' => $category->id,
        ])->assertStatus(403);

        // Attempt duplicate
        $this->actingAs($user1)->post(route('finances.duplicate', $t->id))->assertStatus(403);

        // Attempt delete
        $this->actingAs($user1)->delete(route('finances.destroy', $t->id))->assertStatus(403);
    }

    /**
     * Test a user can pay a credit card invoice (bulk status update to paid).
     */
    public function test_user_can_pay_credit_card_invoice(): void
    {
        $user = User::factory()->create();
        $card = CreditCard::create([
            'user_id' => $user->id,
            'card_name' => 'My Mastercard',
            'bank_name' => 'Nubank',
            'limit' => 5000.00,
            'closing_day' => 10,
            'due_day' => 20,
            'flag' => 'mastercard',
        ]);

        $category = TransactionCategory::where('name', 'Outras Despesas')->first();

        // 1. Transaction to be paid (saida, pendente, correct card, June 2026)
        $t1 = Transaction::create([
            'user_id' => $user->id,
            'credit_card_id' => $card->id,
            'description' => 'Target Expense 1',
            'amount' => 150.00,
            'due_date' => '2026-06-15',
            'status' => 'pendente',
            'type' => 'saida',
            'category_id' => $category->id,
        ]);

        // 2. Another transaction to be paid
        $t2 = Transaction::create([
            'user_id' => $user->id,
            'credit_card_id' => $card->id,
            'description' => 'Target Expense 2',
            'amount' => 50.00,
            'due_date' => '2026-06-20',
            'status' => 'pendente',
            'type' => 'saida',
            'category_id' => $category->id,
        ]);

        // 3. Different month (should not be paid)
        $t3 = Transaction::create([
            'user_id' => $user->id,
            'credit_card_id' => $card->id,
            'description' => 'July Expense',
            'amount' => 200.00,
            'due_date' => '2026-07-15',
            'status' => 'pendente',
            'type' => 'saida',
            'category_id' => $category->id,
        ]);

        // 4. Entrada (should not be paid - only saida is paid by invoices)
        $t4 = Transaction::create([
            'user_id' => $user->id,
            'credit_card_id' => $card->id,
            'description' => 'Credit refund',
            'amount' => 20.00,
            'due_date' => '2026-06-15',
            'status' => 'pendente',
            'type' => 'entrada',
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->post(route('finances.pay-invoice', $card->id), [
            'month' => 6,
            'year' => 2026,
        ]);

        $response->assertRedirect();
        
        $this->assertEquals('pago', $t1->fresh()->status);
        $this->assertNotNull($t1->fresh()->paid_at);

        $this->assertEquals('pago', $t2->fresh()->status);
        $this->assertNotNull($t2->fresh()->paid_at);

        $this->assertEquals('pendente', $t3->fresh()->status);
        $this->assertEquals('pendente', $t4->fresh()->status);
    }

    /**
     * Test other users cannot bulk pay invoice.
     */
    public function test_other_user_cannot_pay_credit_card_invoice(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $card = CreditCard::create([
            'user_id' => $user2->id,
            'card_name' => 'User 2 Card',
            'bank_name' => 'Nubank',
            'limit' => 5000.00,
            'closing_day' => 10,
            'due_day' => 20,
            'flag' => 'mastercard',
        ]);

        $response = $this->actingAs($user1)->post(route('finances.pay-invoice', $card->id), [
            'month' => 6,
            'year' => 2026,
        ]);

        $response->assertStatus(403);
    }
}
