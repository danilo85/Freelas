<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Client;
use App\Models\Project;
use App\Models\ProjectAttachment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProjectAttachmentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test guests cannot access attachment routes.
     */
    public function test_guests_cannot_access_attachment_routes(): void
    {
        $project = Project::factory()->create();
        $attachment = ProjectAttachment::factory()->create([
            'project_id' => $project->id,
            'file_path' => 'attachments/' . $project->id . '/test.pdf'
        ]);

        $this->post(route('projects.attachments.store', $project->id))->assertRedirect(route('login'));
        $this->delete(route('projects.attachments.destroy', $attachment->id))->assertRedirect(route('login'));
        $this->get(route('projects.attachments.download', $attachment->id))->assertRedirect(route('login'));
        $this->patch(route('projects.attachments.classification', $attachment->id))->assertRedirect(route('login'));
    }

    /**
     * Test a user can upload attachments and they are auto-classified.
     */
    public function test_user_can_upload_attachments_with_auto_classification(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $project = Project::factory()->create(['client_id' => $client->id]);

        // 1. Upload tax invoice file
        $invoiceFile = UploadedFile::fake()->create('nota_fiscal_servico.pdf', 500); // 500kb
        
        $response1 = $this->actingAs($user)->postJson(route('projects.attachments.store', $project->id), [
            'file' => $invoiceFile,
            'classification' => 'auto'
        ]);

        $response1->assertStatus(200);
        $response1->assertJsonPath('success', true);
        $this->assertDatabaseHas('project_attachments', [
            'project_id' => $project->id,
            'name' => 'nota_fiscal_servico.pdf',
            'classification' => 'nota_fiscal',
        ]);

        // Assert file exists in fake storage
        $storedPath1 = ProjectAttachment::where('name', 'nota_fiscal_servico.pdf')->first()->file_path;
        Storage::disk('local')->assertExists($storedPath1);

        // 2. Upload material file
        $materialFile = UploadedFile::fake()->create('briefing_projeto.zip', 1000);
        
        $response2 = $this->actingAs($user)->postJson(route('projects.attachments.store', $project->id), [
            'file' => $materialFile,
            'classification' => 'auto'
        ]);

        $response2->assertStatus(200);
        $this->assertDatabaseHas('project_attachments', [
            'project_id' => $project->id,
            'name' => 'briefing_projeto.zip',
            'classification' => 'material',
        ]);

        // 3. Upload general file
        $generalFile = UploadedFile::fake()->create('documento_teste.txt', 100);
        
        $response3 = $this->actingAs($user)->postJson(route('projects.attachments.store', $project->id), [
            'file' => $generalFile,
            'classification' => 'auto'
        ]);

        $response3->assertStatus(200);
        $this->assertDatabaseHas('project_attachments', [
            'project_id' => $project->id,
            'name' => 'documento_teste.txt',
            'classification' => 'anexo',
        ]);
    }

    /**
     * Test user cannot upload attachments to another user's project.
     */
    public function test_user_cannot_upload_attachments_to_others_project(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $client2 = Client::factory()->create(['user_id' => $user2->id]);
        $project2 = Project::factory()->create(['client_id' => $client2->id]);

        $file = UploadedFile::fake()->create('test.pdf', 500);

        $response = $this->actingAs($user1)->postJson(route('projects.attachments.store', $project2->id), [
            'file' => $file,
            'classification' => 'auto'
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test user can update the classification of an attachment.
     */
    public function test_user_can_update_attachment_classification(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $project = Project::factory()->create(['client_id' => $client->id]);
        $attachment = ProjectAttachment::factory()->create([
            'project_id' => $project->id,
            'name' => 'arquivo.pdf',
            'classification' => 'anexo'
        ]);

        $response = $this->actingAs($user)->patchJson(route('projects.attachments.classification', $attachment->id), [
            'classification' => 'nota_fiscal'
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('project_attachments', [
            'id' => $attachment->id,
            'classification' => 'nota_fiscal'
        ]);
    }

    /**
     * Test user can download attachment, and unauthorized user gets 403.
     */
    public function test_attachment_download_authorization(): void
    {
        Storage::fake('local');

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $client1 = Client::factory()->create(['user_id' => $user1->id]);
        $project1 = Project::factory()->create(['client_id' => $client1->id]);
        
        $fakePath = 'attachments/' . $project1->id . '/test.pdf';
        Storage::disk('local')->put($fakePath, 'dummy contents');

        $attachment = ProjectAttachment::factory()->create([
            'project_id' => $project1->id,
            'name' => 'test.pdf',
            'file_path' => $fakePath,
        ]);

        // Owner can download
        $response1 = $this->actingAs($user1)->get(route('projects.attachments.download', $attachment->id));
        $response1->assertStatus(200);
        $this->assertEquals('dummy contents', $response1->streamedContent());

        // Other user cannot download
        $response2 = $this->actingAs($user2)->get(route('projects.attachments.download', $attachment->id));
        $response2->assertStatus(403);
    }

    /**
     * Test user can delete their own attachment.
     */
    public function test_user_can_delete_their_own_attachment(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $project = Project::factory()->create(['client_id' => $client->id]);

        $fakePath = 'attachments/' . $project->id . '/delete_me.pdf';
        Storage::disk('local')->put($fakePath, 'dummy contents');

        $attachment = ProjectAttachment::factory()->create([
            'project_id' => $project->id,
            'name' => 'delete_me.pdf',
            'file_path' => $fakePath,
        ]);

        $response = $this->actingAs($user)->deleteJson(route('projects.attachments.destroy', $attachment->id));
        
        $response->assertStatus(200);
        $this->assertDatabaseMissing('project_attachments', ['id' => $attachment->id]);
        Storage::disk('local')->assertMissing($fakePath);
    }
}
