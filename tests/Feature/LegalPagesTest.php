<?php
namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\User;
use App\Support\DefaultContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegalPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_privacy_and_terms_pages_show_full_content(): void
    {
        $this->get('/privacy-policy')->assertOk()->assertInertia(fn ($page) => $page
            ->component('Public/Page')
            ->where('page', 'privacy-policy')
            ->where('settings.privacy_policy', DefaultContent::privacyPolicy())
            ->where('settings.privacy_policy_updated', '2026-09-15'));

        $this->get('/terms')->assertOk()->assertInertia(fn ($page) => $page
            ->where('settings.terms', DefaultContent::terms())
            ->where('settings.terms_updated', '2026-09-15'));

        $this->assertStringContainsString('## 8. Your rights', DefaultContent::privacyPolicy());
        $this->assertStringContainsString('## 10. Governing law', DefaultContent::terms());
    }

    public function test_admin_can_edit_legal_pages(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        // Over the 10,000-character limit for ordinary settings; no trailing space (TrimStrings would strip it).
        $longText = "## Updated terms\n\n".trim(str_repeat('A long clause about website use. ', 700));

        $this->actingAs($admin)->put('/admin/settings', ['settings' => [
            'terms' => $longText,
            'terms_updated' => '2026-10-01',
        ]])->assertSessionHasNoErrors()->assertRedirect();

        $this->assertSame($longText, SiteSetting::where('key', 'terms')->value('value'));
        $this->get('/terms')->assertInertia(fn ($page) => $page
            ->where('settings.terms', $longText)
            ->where('settings.terms_updated', '2026-10-01'));
    }

    public function test_legal_settings_are_validated(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->put('/admin/settings', ['settings' => ['privacy_policy_updated' => 'next week']])
            ->assertSessionHasErrors('settings.privacy_policy_updated');
    }

    public function test_guests_cannot_edit_legal_pages(): void
    {
        $this->put('/admin/settings', ['settings' => ['terms' => 'Hijacked']])->assertRedirect('/login');

        $this->assertNotSame('Hijacked', SiteSetting::where('key', 'terms')->value('value'));
    }

    public function test_migration_replaces_placeholders_but_keeps_edits(): void
    {
        SiteSetting::where('key', 'privacy_policy')->update(['value' => 'Your privacy matters to us. Contact SAF Partners for details about how enquiries are handled.']);
        SiteSetting::where('key', 'terms')->update(['value' => 'Custom terms written by the owner.']);
        SiteSetting::whereIn('key', ['privacy_policy_updated', 'terms_updated'])->delete();

        (require database_path('migrations/2026_09_15_000002_publish_legal_pages.php'))->up();

        $this->assertSame(DefaultContent::privacyPolicy(), SiteSetting::where('key', 'privacy_policy')->value('value'));
        $this->assertSame('Custom terms written by the owner.', SiteSetting::where('key', 'terms')->value('value'));
        $this->assertSame('2026-09-15', SiteSetting::where('key', 'terms_updated')->value('value'));
    }
}
