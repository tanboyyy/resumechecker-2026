<?php

namespace Tests\Api;

use App\Models\Resume;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
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

    public function test_preview_url_is_signed_and_scoped_to_the_owner(): void
    {
        $resume = Resume::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/resumes/{$resume->id}/preview-url");

        $response->assertOk()->assertJsonStructure(['url', 'expires_in']);
        $this->assertStringContainsString('signature=', $response->json('url'));
    }

    public function test_another_user_cannot_mint_a_preview_url(): void
    {
        $resume = Resume::factory()->create();

        $this->actingAs($this->user)
            ->getJson("/api/resumes/{$resume->id}/preview-url")
            ->assertForbidden();
    }

    public function test_preview_rejects_an_unsigned_request(): void
    {
        $resume = Resume::factory()->create(['user_id' => $this->user->id]);

        $this->get("/api/resumes/{$resume->id}/preview")->assertForbidden();
    }

    public function test_preview_rejects_a_tampered_signature(): void
    {
        $resume = Resume::factory()->create(['user_id' => $this->user->id]);

        $url = URL::temporarySignedRoute('resumes.preview', now()->addMinutes(10), ['resume' => $resume->id]);

        $this->get($url . 'x')->assertForbidden();
    }

    public function test_preview_rejects_an_expired_signature(): void
    {
        $resume = Resume::factory()->create(['user_id' => $this->user->id]);

        $url = URL::temporarySignedRoute('resumes.preview', now()->addMinutes(10), ['resume' => $resume->id]);

        $this->travel(11)->minutes();

        $this->get($url)->assertForbidden();
    }

    public function test_download_returns_404_when_the_stored_file_is_missing(): void
    {
        $resume = Resume::factory()->create([
            'user_id' => $this->user->id,
            'storage_path' => 'resumes/does-not-exist.pdf',
        ]);

        $this->actingAs($this->user)
            ->get("/api/resumes/{$resume->id}/download")
            ->assertNotFound();
    }
}
