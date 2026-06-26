<?php

namespace Database\Factories;

use App\Models\ProjectAttachment;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProjectAttachment>
 */
class ProjectAttachmentFactory extends Factory
{
    protected $model = ProjectAttachment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'name' => $this->faker->word() . '.pdf',
            'file_path' => 'attachments/test.pdf',
            'file_size' => $this->faker->numberBetween(1024, 1024 * 1024 * 5), // 1KB to 5MB
            'mime_type' => 'application/pdf',
            'classification' => 'anexo',
        ];
    }
}
