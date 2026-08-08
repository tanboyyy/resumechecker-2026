<?php

namespace Tests\Api;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_user_can_list_plans(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/billing/plans');

        $response->assertOk()
            ->assertJsonStructure(['plans']);
    }

    public function test_user_can_get_subscription(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/billing/subscription');

        $response->assertOk()
            ->assertJsonFragment(['plan' => 'free']);
    }

    public function test_user_can_get_usage(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/billing/usage');

        $response->assertOk()
            ->assertJsonStructure([
                'analyses_used',
                'analysis_limit',
                'resumes_count',
            ]);
    }

    public function test_user_with_active_subscription(): void
    {
        Subscription::create([
            'user_id' => $this->user->id,
            'stripe_id' => 'sub_test123',
            'stripe_status' => 'active',
            'plan' => 'pro',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/billing/subscription');

        $response->assertOk()
            ->assertJsonFragment(['plan' => 'pro', 'status' => 'active']);
    }

    public function test_unauthenticated_user_cannot_access_billing(): void
    {
        $response = $this->getJson('/api/billing/subscription');

        $response->assertUnauthorized();
    }
}
