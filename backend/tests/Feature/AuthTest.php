<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_csrf_token_endpoint(): void
    {
        $response = $this->getJson('/api/auth/csrf');

        $response->assertOk()
            ->assertJsonStructure(['csrf_token']);
    }

    public function test_unauthenticated_user_gets_401(): void
    {
        $response = $this->getJson('/api/auth/user');

        $response->assertUnauthorized();
    }

    public function test_authenticated_user_can_get_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson('/api/auth/user');

        $response->assertOk()
            ->assertJsonFragment([
                'email' => $user->email,
                'name' => $user->name,
            ]);
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/auth/logout');

        $response->assertOk()
            ->assertJsonFragment(['message' => 'Logged out']);
    }
}
