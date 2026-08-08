<?php

namespace Tests\Api;

use App\Models\Analysis;
use App\Models\Resume;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AnalysisApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Resume $resume;

    protected function setUp(): void
    {
        parent::setUp();

        // Factory resumes have no file behind them, so let the extraction job
        // stay queued rather than run and correctly mark them unreadable.
        Queue::fake();

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

    public function test_analysis_cannot_be_created_while_extraction_is_pending(): void
    {
        $resume = Resume::factory()->pendingExtraction()->create(['user_id' => $this->user->id]);

        $this->actingAs($this->user)
            ->postJson("/api/resumes/{$resume->id}/analyses", ['type' => 'ats'])
            ->assertForbidden();
    }

    public function test_analysis_cannot_be_created_for_an_unreadable_resume(): void
    {
        $resume = Resume::factory()->unreadable()->create(['user_id' => $this->user->id]);

        $this->actingAs($this->user)
            ->postJson("/api/resumes/{$resume->id}/analyses", ['type' => 'ats'])
            ->assertForbidden();
    }

    public function test_free_plan_cannot_use_a_paid_analysis_type(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/resumes/{$this->resume->id}/analyses", ['type' => 'formatting']);

        $response->assertForbidden()
            ->assertJsonFragment(['requested_type' => 'formatting'])
            ->assertJsonPath('allowed_types', ['ats']);

        $this->assertDatabaseMissing('analyses', ['type' => 'formatting']);
    }

    public function test_pro_plan_can_use_a_paid_analysis_type(): void
    {
        \App\Models\Subscription::create([
            'user_id' => $this->user->id,
            'stripe_id' => 'sub_pro_test',
            'stripe_status' => 'active',
            'plan' => 'pro',
        ]);

        $this->actingAs($this->user)
            ->postJson("/api/resumes/{$this->resume->id}/analyses", ['type' => 'formatting'])
            ->assertCreated();
    }

    public function test_failed_analyses_do_not_count_against_the_monthly_quota(): void
    {
        // The free plan allows three; five failures must not exhaust it.
        Analysis::factory()->count(5)->create([
            'resume_id' => $this->resume->id,
            'status' => 'failed',
        ]);

        $this->actingAs($this->user)
            ->postJson("/api/resumes/{$this->resume->id}/analyses", ['type' => 'ats'])
            ->assertCreated();
    }

    public function test_the_monthly_quota_is_enforced(): void
    {
        Analysis::factory()->count(3)->create([
            'resume_id' => $this->resume->id,
            'status' => 'completed',
        ]);

        $this->actingAs($this->user)
            ->postJson("/api/resumes/{$this->resume->id}/analyses", ['type' => 'ats'])
            ->assertForbidden()
            ->assertJsonFragment(['limit' => 3]);
    }
}
