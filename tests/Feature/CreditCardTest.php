<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\CreditCard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreditCardTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test guests are redirected from credit card routes.
     */
    public function test_guests_cannot_access_credit_card_routes(): void
    {
        $this->get(route('credit-cards.create'))->assertRedirect(route('login'));
        $this->post(route('credit-cards.store'))->assertRedirect(route('login'));
    }

    /**
     * Test a user can create a credit card.
     */
    public function test_user_can_create_credit_card(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('credit-cards.store'), [
            'card_name' => 'Nubank Violeta',
            'bank_name' => 'Nubank',
            'limit' => 'R$ 5.000,00',
            'closing_day' => '5',
            'due_day' => '12',
            'flag' => 'visa',
            'last_four_digits' => '4321',
            'observations' => 'Cartão de despesas de marketing',
        ]);

        $response->assertRedirect(route('bank-accounts.index', ['tab' => 'cards']));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('credit_cards', [
            'user_id' => $user->id,
            'card_name' => 'Nubank Violeta',
            'bank_name' => 'Nubank',
            'limit' => 5000.00,
            'closing_day' => 5,
            'due_day' => 12,
            'flag' => 'visa',
            'last_four_digits' => '4321',
        ]);
    }

    /**
     * Test a user can create a credit card with custom/other bank.
     */
    public function test_user_can_create_custom_bank_credit_card(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('credit-cards.store'), [
            'card_name' => 'Cartão Viacredi',
            'bank_name' => 'Outro',
            'custom_bank_name' => 'Cooperativa Viacredi',
            'limit' => '1.500,00',
            'closing_day' => '10',
            'due_day' => '20',
            'flag' => 'mastercard',
            'last_four_digits' => '9988',
        ]);

        $response->assertRedirect(route('bank-accounts.index', ['tab' => 'cards']));

        $this->assertDatabaseHas('credit_cards', [
            'user_id' => $user->id,
            'bank_name' => 'Cooperativa Viacredi',
            'card_name' => 'Cartão Viacredi',
            'limit' => 1500.00,
            'flag' => 'mastercard',
            'last_four_digits' => '9988',
        ]);
    }

    /**
     * Test a user can list only their own credit cards.
     */
    public function test_user_can_only_list_their_own_credit_cards(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        CreditCard::create([
            'user_id' => $user1->id,
            'card_name' => 'User 1 Card',
            'bank_name' => 'Nubank',
            'limit' => 1000.00,
            'closing_day' => 5,
            'due_day' => 12,
            'flag' => 'visa',
        ]);

        CreditCard::create([
            'user_id' => $user2->id,
            'card_name' => 'User 2 Card',
            'bank_name' => 'Itaú',
            'limit' => 2000.00,
            'closing_day' => 10,
            'due_day' => 20,
            'flag' => 'mastercard',
        ]);

        $response = $this->actingAs($user1)->get(route('bank-accounts.index', ['tab' => 'cards']));

        $response->assertStatus(200);
        $response->assertSee('User 1 Card');
        $response->assertDontSee('User 2 Card');
    }

    /**
     * Test tenancy security checks on edit/update/destroy.
     */
    public function test_user_cannot_modify_others_credit_cards(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $card = CreditCard::create([
            'user_id' => $user2->id,
            'card_name' => 'User 2 Card',
            'bank_name' => 'Itaú',
            'limit' => 2000.00,
            'closing_day' => 10,
            'due_day' => 20,
            'flag' => 'mastercard',
        ]);

        // Attempt edit
        $this->actingAs($user1)
            ->get(route('credit-cards.edit', $card->id))
            ->assertStatus(403);

        // Attempt update
        $this->actingAs($user1)
            ->put(route('credit-cards.update', $card->id), [
                'card_name' => 'Hacked Card',
                'bank_name' => 'Nubank',
                'limit' => '1.00',
                'closing_day' => 1,
                'due_day' => 10,
                'flag' => 'visa',
            ])
            ->assertStatus(403);

        // Attempt delete
        $this->actingAs($user1)
            ->delete(route('credit-cards.destroy', $card->id))
            ->assertStatus(403);
    }

    /**
     * Test user can update their credit card.
     */
    public function test_user_can_update_their_credit_card(): void
    {
        $user = User::factory()->create();
        $card = CreditCard::create([
            'user_id' => $user->id,
            'card_name' => 'Old Card Name',
            'bank_name' => 'Nubank',
            'limit' => 1000.00,
            'closing_day' => 5,
            'due_day' => 12,
            'flag' => 'visa',
        ]);

        $response = $this->actingAs($user)->put(route('credit-cards.update', $card->id), [
            'card_name' => 'New Card Name',
            'bank_name' => 'Itaú',
            'limit' => 'R$ 2.500,50',
            'closing_day' => '10',
            'due_day' => '20',
            'flag' => 'mastercard',
            'last_four_digits' => '9999',
            'observations' => 'Updated observations',
        ]);

        $response->assertRedirect(route('bank-accounts.index', ['tab' => 'cards']));
        
        $this->assertDatabaseHas('credit_cards', [
            'id' => $card->id,
            'card_name' => 'New Card Name',
            'bank_name' => 'Itaú',
            'limit' => 2500.50,
            'closing_day' => 10,
            'due_day' => 20,
            'flag' => 'mastercard',
            'last_four_digits' => '9999',
            'observations' => 'Updated observations',
        ]);
    }

    /**
     * Test user can delete their credit card.
     */
    public function test_user_can_delete_their_credit_card(): void
    {
        $user = User::factory()->create();
        $card = CreditCard::create([
            'user_id' => $user->id,
            'card_name' => 'Card to Delete',
            'bank_name' => 'Nubank',
            'limit' => 1000.00,
            'closing_day' => 5,
            'due_day' => 12,
            'flag' => 'visa',
        ]);

        $response = $this->actingAs($user)->delete(route('credit-cards.destroy', $card->id));
 
         $response->assertRedirect(route('bank-accounts.index', ['tab' => 'cards']));
         $this->assertDatabaseMissing('credit_cards', ['id' => $card->id]);
     }
 
     /**
      * Test authenticated user can access create and edit views.
      */
     public function test_authenticated_user_can_access_create_and_edit_views(): void
     {
         $user = User::factory()->create();
         
         // Access create view
         $response = $this->actingAs($user)->get(route('credit-cards.create'));
         $response->assertStatus(200);
 
         // Access edit view
         $card = CreditCard::create([
             'user_id' => $user->id,
             'card_name' => 'Nubank Card',
             'bank_name' => 'Nubank',
             'limit' => 1000.00,
             'closing_day' => 5,
             'due_day' => 12,
             'flag' => 'visa',
         ]);
 
         $response = $this->actingAs($user)->get(route('credit-cards.edit', $card->id));
         $response->assertStatus(200);
     }
 }
