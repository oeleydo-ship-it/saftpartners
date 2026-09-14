<?php
namespace Tests\Feature;

use App\Models\ContactSubmission;
use App\Models\Market;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SafPartnersTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_submission_is_validated_and_persisted(): void
    {
        $this->post('/contact', [
            'name' => 'A Candidate', 'email' => 'candidate@example.com',
            'message' => 'I would like to discuss an executive search mandate.', 'consent' => true,
        ])->assertSessionHas('success');
        $this->assertDatabaseHas(ContactSubmission::class, ['email' => 'candidate@example.com', 'status' => 'new']);

        $this->post('/contact', ['name' => '<script>alert(1)</script>', 'email' => 'invalid'])
            ->assertSessionHasErrors(['email', 'message', 'consent']);
    }

    public function test_admin_is_protected_and_roles_are_enforced(): void
    {
        $this->get('/admin')->assertRedirect('/login');
        $editor = User::factory()->create(['role' => 'editor']);
        $market = Market::create(['title' => 'Test', 'slug' => 'test']);
        $this->actingAs($editor)->delete("/admin/markets/{$market->id}")->assertForbidden();
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->delete("/admin/markets/{$market->id}")->assertRedirect();
    }

    public function test_executable_and_svg_uploads_are_rejected(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->post('/admin/media', [
            'file' => UploadedFile::fake()->create('payload.php.jpg', 10, 'application/x-php'),
        ])->assertSessionHasErrors('file');
        $this->actingAs($admin)->post('/admin/media', [
            'file' => UploadedFile::fake()->create('attack.svg', 10, 'image/svg+xml'),
        ])->assertSessionHasErrors('file');
    }
}
