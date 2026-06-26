<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Transaction;
use App\Models\TransactionCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MeiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test guest cannot access MEI dashboard.
     */
    public function test_guests_cannot_access_mei_routes(): void
    {
        $this->get(route('finances.mei'))->assertRedirect(route('login'));
        $this->post(route('finances.mei.limit'))->assertRedirect(route('login'));
    }

    /**
     * Test MEI faturamento calculations.
     */
    public function test_mei_faturamento_calculations_and_thermometer(): void
    {
        $user = User::factory()->create(['mei_limit' => 80000.00]);
        $category = TransactionCategory::where('name', 'Freelance / Projetos')->first();

        // paid PJ income (counts)
        Transaction::create([
            'user_id' => $user->id,
            'description' => 'Faturamento Web',
            'amount' => 20000.00,
            'due_date' => '2026-06-01',
            'status' => 'pago',
            'type' => 'entrada',
            'classification' => 'PJ',
            'category_id' => $category->id
        ]);

        // pending PJ income (does not count toward MEI thermometer faturamento)
        Transaction::create([
            'user_id' => $user->id,
            'description' => 'Faturamento Pendente',
            'amount' => 5000.00,
            'due_date' => '2026-06-02',
            'status' => 'pendente',
            'type' => 'entrada',
            'classification' => 'PJ',
            'category_id' => $category->id
        ]);

        // paid PF income (does not count toward MEI PJ thermometer faturamento)
        Transaction::create([
            'user_id' => $user->id,
            'description' => 'Faturamento PF',
            'amount' => 10000.00,
            'due_date' => '2026-06-03',
            'status' => 'pago',
            'type' => 'entrada',
            'classification' => 'PF',
            'category_id' => $category->id
        ]);

        $response = $this->actingAs($user)->get(route('finances.mei', ['year' => 2026]));
        
        $response->assertStatus(200);
        $response->assertSee('R$ 20.000,00'); // counts
        $response->assertDontSee('R$ 30.000,00'); // should not combine PF or pending PJ in total faturamento
        
        // Termômetro: 20000 / 80000 = 25%
        $response->assertSee('25%'); 
    }

    /**
     * Test updating MEI annual limit.
     */
    public function test_user_can_update_mei_limit(): void
    {
        $user = User::factory()->create(['mei_limit' => 81000.00]);

        $response = $this->actingAs($user)->post(route('finances.mei.limit'), [
            'mei_limit' => 'R$ 100.000,00',
        ]);

        $response->assertRedirect(route('finances.mei'));
        $this->assertEquals(100000.00, $user->fresh()->mei_limit);
    }
}
