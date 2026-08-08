<?php

namespace Tests\Api;

use App\Models\Analysis;
use App\Models\Resume;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalysisApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Resume $resume;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->resume = Resume::factory()->create(['user_id' => $this->user->id]);
    }

    public function test_user_can_list_analyses(): void
    {
        Analysis::factory()->count(3)->create(['resume_id' => $this->resume->id]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/resumes/{$this->resume->id}/analyses");

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_user_can_create_analysis(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/resumes/{$this->resume->id}/analyses", [
                'type' => 'ats',
            ]);

        $response->assertCreated()
            ->assertJsonFragment(['type' => 'ats', 'status' => 'pending']);

        $this->assertDatabaseHas('analyses', [
            'resume_id' => $this->resume->id,
            'type' => 'ats',
        ]);
    }

    public function test_user_cannot_create_analysis_for_other_users_resume(): void
    {
        $otherResume = Resume::factory()->create();

        $response = $this->actingAs($this->user)
            ->postJson("/api/resumes/{$otherResume->id}/analyses", [
                'type' => 'ats',
            ]);

        $response->assertForbidden();
    }

    public function test_user_can_show_analysis(): void
    {
        $analysis = Analysis::factory()->create(['resume_id' => $this->resume->id]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/resumes/{$this->resume->id}/analyses/{$analysis->id}");

        $response->assertOk()
            ->assertJsonFragment(['id' => $analysis->id]);
    }

    public function test_user_can_delete_analysis(): void
    {
        $analysis = Analysis::factory()->create(['resume_id' => $this->resume->id]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/resumes/{$this->resume->id}/analyses/{$analysis->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('analyses', ['id' => $analysis->id]);
    }

    public function test_unauthenticated_user_cannot_access_analyses(): void
    {
        $response = $this->getJson("/api/resumes/{$this->resume->id}/analyses");

        $response->assertUnauthorized();
    }
}
