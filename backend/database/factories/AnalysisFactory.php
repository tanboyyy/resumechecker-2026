<?php

namespace Database\Factories;

use App\Models\Analysis;
use App\Models\Resume;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnalysisFactory extends Factory
{
    protected $model = Analysis::class;

    public function definition(): array
    {
        return [
            'resume_id' => Resume::factory(),
            'type' => $this->faker->randomElement(['ats', 'content', 'formatting', 'comparison']),
            'status' => 'completed',
            'ats_score' => $this->faker->numberBetween(40, 95),
            'raw_response' => [
                'summary' => $this->faker->paragraph(),
                'score' => $this->faker->numberBetween(40, 95),
            ],
            'tokens_used' => $this->faker->numberBetween(500, 2000),
            'completed_at' => now(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'status' => 'pending',
            'ats_score' => null,
            'raw_response' => null,
            'completed_at' => null,
        ]);
    }

    public function processing(): static
    {
        return $this->state(fn () => [
            'status' => 'processing',
            'ats_score' => null,
            'completed_at' => null,
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn () => [
            'status' => 'failed',
            'error_message' => 'OpenAI API request failed',
            'ats_score' => null,
            'completed_at' => null,
        ]);
    }
}
