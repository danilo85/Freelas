<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Author;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AuthorTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test guests are redirected to login.
     */
    public function test_guests_cannot_access_authors_routes(): void
    {
        $this->get(route('authors.index'))->assertRedirect(route('login'));
        $this->get(route('authors.create'))->assertRedirect(route('login'));
        $this->post(route('authors.store'))->assertRedirect(route('login'));
    }

    /**
     * Test authenticated user can access authors index and see only their authors.
     */
    public function test_user_can_only_see_their_own_authors(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $author1 = Author::factory()->create([
            'user_id' => $user1->id,
            'name' => 'Autor do Usuario 1',
            'bio' => 'Biografia do primeiro autor',
        ]);

        $author2 = Author::factory()->create([
            'user_id' => $user2->id,
            'name' => 'Autor do Usuario 2',
            'bio' => 'Biografia do segundo autor',
        ]);

        $response = $this->actingAs($user1)->get(route('authors.index'));

        $response->assertStatus(200);
        $response->assertSee('Autor do Usuario 1');
        $response->assertSee('Biografia do primeiro autor');
        $response->assertDontSee('Autor do Usuario 2');
        $response->assertDontSee('Biografia do segundo autor');
    }

    /**
     * Test user can register an author.
     */
    public function test_user_can_create_author(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $avatar = UploadedFile::fake()->image('avatar.jpg');

        $data = [
            'name' => 'Machado de Assis',
            'email' => 'machado@academia.org.br',
            'phone' => '(21) 99999-9999',
            'document' => '123.456.789-00',
            'bio' => 'Um dos maiores escritores da literatura brasileira.',
            'avatar' => $avatar,
        ];

        $response = $this->actingAs($user)->post(route('authors.store'), $data);

        $response->assertRedirect(route('authors.index'));
        $this->assertDatabaseHas('authors', [
            'user_id' => $user->id,
            'name' => 'Machado de Assis',
            'email' => 'machado@academia.org.br',
            'bio' => 'Um dos maiores escritores da literatura brasileira.',
        ]);

        $author = Author::where('email', 'machado@academia.org.br')->first();
        $this->assertNotNull($author->avatar);
        Storage::disk('public')->assertExists($author->avatar);
    }

    /**
     * Test user can edit their own author.
     */
    public function test_user_can_edit_their_own_author(): void
    {
        $user = User::factory()->create();
        $author = Author::factory()->create([
            'user_id' => $user->id,
            'name' => 'Nome Antigo',
            'bio' => 'Bio Antiga',
            'registration_completed' => false,
        ]);

        $response = $this->actingAs($user)->get(route('authors.edit', $author));
        $response->assertStatus(200);

        $data = [
            'name' => 'Nome Novo',
            'email' => $author->email,
            'phone' => '(21) 88888-8888',
            'document' => $author->document,
            'bio' => 'Bio Nova',
        ];

        $response = $this->actingAs($user)->put(route('authors.update', $author), $data);
        $response->assertRedirect(route('authors.index'));

        $this->assertDatabaseHas('authors', [
            'id' => $author->id,
            'name' => 'Nome Novo',
            'bio' => 'Bio Nova',
            'registration_completed' => true,
        ]);
    }

    /**
     * Test user cannot access or modify other user's author.
     */
    public function test_user_cannot_access_or_modify_other_users_author(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $authorOfUser2 = Author::factory()->create([
            'user_id' => $user2->id,
            'name' => 'Autor de Outro',
        ]);

        // Tentar visualizar
        $this->actingAs($user1)->get(route('authors.show', $authorOfUser2))->assertStatus(403);

        // Tentar editar
        $this->actingAs($user1)->get(route('authors.edit', $authorOfUser2))->assertStatus(403);

        // Tentar atualizar
        $this->actingAs($user1)->put(route('authors.update', $authorOfUser2), [
            'name' => 'Invasor',
            'email' => 'invasor@exemplo.com',
        ])->assertStatus(403);

        // Tentar deletar
        $this->actingAs($user1)->delete(route('authors.destroy', $authorOfUser2))->assertStatus(403);
    }

    /**
     * Test user can view their own author details.
     */
    public function test_user_can_view_their_own_author(): void
    {
        $user = User::factory()->create();
        $author = Author::factory()->create([
            'user_id' => $user->id,
            'name' => 'Machado de Assis',
            'bio' => 'Maior escritor do Brasil',
        ]);

        $response = $this->actingAs($user)->get(route('authors.show', $author));

        $response->assertStatus(200);
        $response->assertSee('Machado de Assis');
        $response->assertSee('Maior escritor do Brasil');
    }

    /**
     * Test user can delete their own author.
     */
    public function test_user_can_delete_their_own_author(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $avatarPath = Storage::disk('public')->putFile('avatars', UploadedFile::fake()->image('avatar.jpg'));

        $author = Author::factory()->create([
            'user_id' => $user->id,
            'avatar' => $avatarPath,
        ]);

        Storage::disk('public')->assertExists($avatarPath);

        $response = $this->actingAs($user)->delete(route('authors.destroy', $author));
        $response->assertRedirect(route('authors.index'));

        $this->assertDatabaseMissing('authors', ['id' => $author->id]);
        Storage::disk('public')->assertMissing($avatarPath);
    }
}
