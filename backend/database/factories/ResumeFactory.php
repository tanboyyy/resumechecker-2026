<?php

namespace Database\Factories;

use App\Models\Resume;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ResumeFactory extends Factory
{
    protected $model = Resume::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->words(3, true),
            'original_filename' => $this->faker->uuid . '.pdf',
            'storage_path' => 'resumes/' . $this->faker->uuid . '.pdf',
            'disk' => 'local',
            'mime_type' => 'application/pdf',
            'size' => $this->faker->numberBetween(10000, 5000000),
            'text_extracted' => true,
            'extraction_status' => 'completed',
            'extracted_text' => $this->faker->paragraphs(3, true),
        ];
    }

    /** A resume whose text could not be read, so it cannot be analysed. */
    public function unreadable(): static
    {
        return $this->state([
            'text_extracted' => false,
            'extraction_status' => 'failed',
            'extraction_error' => 'We could not read any text from this file.',
            'extracted_text' => null,
        ]);
    }

    /** A resume still waiting on the extraction worker. */
    public function pendingExtraction(): static
    {
        return $this->state([
            'text_extracted' => false,
            'extraction_status' => 'pending',
            'extracted_text' => null,
        ]);
    }
}
