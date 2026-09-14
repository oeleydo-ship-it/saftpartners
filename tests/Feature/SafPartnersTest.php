<?php
namespace Tests\Feature;

use App\Mail\ContactSubmissionReceived;
use App\Models\ContactSubmission;
use App\Models\Market;
use App\Models\User;
use App\Services\SpamGuard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SafPartnersTest extends TestCase
{
    use RefreshDatabase;

    /** A human-looking submission: token issued, then a few seconds pass before sending. */
    private function sendContact(array $overrides = [], int $secondsLater = 10)
    {
        $payload = array_merge([
            'name' => 'A Candidate', 'email' => 'candidate@example.com',
            'message' => 'I would like to discuss an executive search mandate.', 'consent' => true,
            'form_token' => SpamGuard::token(),
        ], $overrides);
        $this->travel($secondsLater)->seconds();

        return $this->from('/')->post('/contact', $payload);
    }

    public function test_contact_submission_is_validated_and_persisted(): void
    {
        $this->sendContact()->assertSessionHas('success');
        $this->assertDatabaseHas(ContactSubmission::class, ['email' => 'candidate@example.com', 'status' => 'new']);

        $this->post('/contact', ['name' => '<script>alert(1)</script>', 'email' => 'invalid'])
            ->assertSessionHasErrors(['email', 'message', 'consent']);
    }

    public function test_home_page_issues_a_form_token(): void
    {
        $this->get('/')->assertInertia(fn ($page) => $page->component('Public/Home')->has('formToken'));
    }

    public function test_honeypot_submissions_are_silently_dropped(): void
    {
        $this->sendContact(['website' => 'https://spam.example'])->assertSessionHas('success')->assertSessionHasNoErrors();
        $this->assertDatabaseCount(ContactSubmission::class, 0);
    }

    public function test_missing_or_forged_tokens_are_silently_dropped(): void
    {
        $this->sendContact(['form_token' => null])->assertSessionHas('success');
        $this->sendContact(['form_token' => 'forged', 'email' => 'other@example.com'])->assertSessionHas('success');
        $this->assertDatabaseCount(ContactSubmission::class, 0);
    }

    public function test_instant_submissions_are_silently_dropped(): void
    {
        $this->sendContact(secondsLater: 1)->assertSessionHas('success');
        $this->assertDatabaseCount(ContactSubmission::class, 0);
    }

    public function test_expired_forms_ask_the_sender_to_try_again(): void
    {
        $this->sendContact(secondsLater: SpamGuard::MAX_AGE_SECONDS + 60)->assertSessionHasErrors('form_token');
        $this->assertDatabaseCount(ContactSubmission::class, 0);
    }

    public function test_link_heavy_messages_are_quarantined_without_notification(): void
    {
        Mail::fake();
        config(['mail.contact_to' => 'team@example.com']);

        $this->sendContact(['message' => 'Buy now http://a.test http://b.test http://c.test'])->assertSessionHas('success');
        $this->sendContact(['email' => 'b@example.com', 'message' => 'Great offer [url=http://a.test]click[/url]'])->assertSessionHas('success');

        $this->assertDatabaseCount(ContactSubmission::class, 2);
        $this->assertSame(2, ContactSubmission::where('status', 'spam')->count());
        Mail::assertNothingQueued();
    }

    public function test_clean_messages_notify_the_team(): void
    {
        Mail::fake();
        config(['mail.contact_to' => 'team@example.com']);

        $this->sendContact(['message' => 'Please see our careers page at https://example.com for the role.']);

        Mail::assertQueued(ContactSubmissionReceived::class);
    }

    public function test_duplicate_messages_are_silently_dropped(): void
    {
        $this->sendContact()->assertSessionHas('success');
        $this->sendContact()->assertSessionHas('success');
        $this->assertDatabaseCount(ContactSubmission::class, 1);
    }

    public function test_contact_form_is_rate_limited(): void
    {
        foreach (range(1, 3) as $i) {
            $this->sendContact(['email' => "person{$i}@example.com"], 1 + SpamGuard::MIN_SECONDS)->assertSessionHasNoErrors();
        }
        $this->sendContact(['email' => 'person4@example.com'], 0)->assertSessionHasErrors('form');
        $this->assertDatabaseCount(ContactSubmission::class, 3);
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
