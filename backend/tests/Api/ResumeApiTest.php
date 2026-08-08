<?php

namespace Tests\Api;

use App\Models\Resume;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ResumeApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_user_can_list_resumes(): void
    {
        Resume::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/resumes');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_user_can_upload_resume(): void
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('resume.pdf', 100, 'application/pdf');

        $response = $this->actingAs($this->user)
            ->postJson('/api/resumes', [
                'file' => $file,
                'title' => 'Test Resume',
            ]);

        $response->assertCreated()
            ->assertJsonFragment(['title' => 'Test Resume']);

        $this->assertDatabaseHas('resumes', [
            'user_id' => $this->user->id,
            'title' => 'Test Resume',
        ]);
    }

    public function test_user_cannot_upload_invalid_file_type(): void
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('resume.exe', 100, 'application/x-executable');

        $response = $this->actingAs($this->user)
            ->postJson('/api/resumes', [
                'file' => $file,
            ]);

        $response->assertUnprocessable();
    }

    public function test_user_can_show_resume(): void
    {
        $resume = Resume::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/resumes/{$resume->id}");

        $response->assertOk()
            ->assertJsonFragment(['id' => $resume->id]);
    }

    public function test_user_cannot_show_other_users_resume(): void
    {
        $resume = Resume::factory()->create();

        $response = $this->actingAs($this->user)
            ->getJson("/api/resumes/{$resume->id}");

        $response->assertForbidden();
    }

    public function test_user_can_delete_resume(): void
    {
        Storage::fake('local');
        $resume = Resume::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/resumes/{$resume->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('resumes', ['id' => $resume->id]);
    }

    public function test_unauthenticated_user_cannot_access_resumes(): void
    {
        $response = $this->getJson('/api/resumes');

        $response->assertUnauthorized();
    }
}
