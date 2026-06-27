<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Client;
use App\Models\Author;
use App\Models\Project;
use App\Models\PortfolioCategory;
use App\Models\PortfolioItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PortfolioTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test guests are redirected to login.
     */
    public function test_guests_cannot_access_portfolio_routes(): void
    {
        $this->get(route('portfolio.index'))->assertRedirect(route('login'));
        $this->get(route('portfolio.pipeline'))->assertRedirect(route('login'));
        $this->get(route('portfolio-categories.index'))->assertRedirect(route('login'));
    }

    /**
     * Test user can only see their own categories.
     */
    public function test_user_can_only_see_their_own_portfolio_categories(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $cat1 = PortfolioCategory::create([
            'user_id' => $user1->id,
            'name' => 'Categoria User 1',
            'slug' => 'categoria-user-1',
        ]);

        $cat2 = PortfolioCategory::create([
            'user_id' => $user2->id,
            'name' => 'Categoria User 2',
            'slug' => 'categoria-user-2',
        ]);

        $response = $this->actingAs($user1)->get(route('portfolio-categories.index'));

        $response->assertStatus(200);
        $response->assertSee('Categoria User 1');
        $response->assertDontSee('Categoria User 2');
    }

    /**
     * Test user can create category.
     */
    public function test_user_can_create_portfolio_category(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('portfolio-categories.store'), [
            'name' => 'Nova Categoria',
        ]);

        $response->assertRedirect(route('portfolio-categories.index'));
        $this->assertDatabaseHas('portfolio_categories', [
            'user_id' => $user->id,
            'name' => 'Nova Categoria',
            'slug' => 'nova-categoria',
        ]);
    }

    /**
     * Test user can import project from pipeline and create portfolio item.
     */
    public function test_user_can_create_portfolio_item_and_upload_images(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $author = Author::factory()->create(['user_id' => $user->id]);
        $cat = PortfolioCategory::create([
            'user_id' => $user->id,
            'name' => 'Web Design',
            'slug' => 'web-design',
        ]);

        // Projeto finalizado no pipeline
        $project = Project::factory()->create([
            'client_id' => $client->id,
            'title' => 'Projeto Concluido',
            'status' => 'finalizado',
        ]);

        $thumb = UploadedFile::fake()->image('cover.jpg');
        $gallery1 = UploadedFile::fake()->image('photo1.jpg');
        $gallery2 = UploadedFile::fake()->image('photo2.jpg');

        $response = $this->actingAs($user)->post(route('portfolio.store'), [
            'title' => 'Meu Lindo Trabalho',
            'portfolio_category_id' => $cat->id,
            'description' => 'Descricao detalhada do trabalho',
            'thumb' => $thumb,
            'status' => 'publicado',
            'is_featured' => 1,
            'client_id' => $client->id,
            'project_id' => $project->id,
            'technologies' => 'PHP, Laravel',
            'authors' => [$author->id],
            'gallery' => [$gallery1, $gallery2],
            'gallery_orders' => [1, 2],
        ]);

        $response->assertRedirect(route('portfolio.index'));

        // Verifica item no banco
        $item = PortfolioItem::where('title', 'Meu Lindo Trabalho')->first();
        $this->assertNotNull($item);
        $this->assertEquals($cat->id, $item->portfolio_category_id);
        $this->assertEquals($project->id, $item->project_id);
        $this->assertEquals(1, $item->is_featured);

        // Verifica relacionamento com autor
        $this->assertTrue($item->authors->contains($author->id));

        // Verifica imagens da galeria no banco
        $this->assertCount(2, $item->images);
        
        // Verifica se a capa/thumb foi otimizada e salva como WebP no Storage
        $this->assertNotNull($item->thumb_path);
        Storage::disk('public')->assertExists($item->thumb_path);
    }

    /**
     * Test user can view their own portfolio item detail page.
     */
    public function test_user_can_view_their_own_portfolio_item(): void
    {
        $user = User::factory()->create();
        $cat = PortfolioCategory::create([
            'user_id' => $user->id,
            'name' => 'Web Design',
            'slug' => 'web-design',
        ]);
        $item = PortfolioItem::create([
            'user_id' => $user->id,
            'portfolio_category_id' => $cat->id,
            'title' => 'Trabalho Secreto',
            'slug' => 'trabalho-secreto',
            'description' => 'Uma descricao secreta',
            'status' => 'rascunho',
        ]);

        $response = $this->actingAs($user)->get(route('portfolio.show', $item));

        $response->assertStatus(200);
        $response->assertSee('Trabalho Secreto');
        $response->assertSee('Uma descricao secreta');
    }
}
