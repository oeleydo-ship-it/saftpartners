<?php
namespace Tests\Feature;

use App\Models\MailSetting;
use App\Models\User;
use App\Services\Mail\MailConfigurator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Tests\TestCase;

class MailSettingsTest extends TestCase
{
    use RefreshDatabase;

    private const CLIENT_ID = '11111111-2222-3333-4444-555555555555';

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        config(['mail.mailers.log.channel' => 'null']);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function m365Payload(array $overrides = []): array
    {
        return array_merge([
            'mailer' => 'microsoft365', 'username' => 'contact@safpartners.ae',
            'ms_tenant_id' => 'safpartners.onmicrosoft.com', 'ms_client_id' => self::CLIENT_ID, 'ms_client_secret' => 'super-secret-value',
            'from_address' => 'contact@safpartners.ae', 'from_name' => 'SAF Partners', 'contact_to' => 'team@safpartners.ae',
        ], $overrides);
    }

    public function test_admin_can_save_smtp_settings_and_secrets_are_encrypted_and_hidden(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->put('/admin/mail', [
            'mailer' => 'smtp', 'host' => 'smtp.example.com', 'port' => 587, 'encryption' => 'tls',
            'username' => 'mailer@example.com', 'password' => 'p@ssw0rd-secret', 'from_address' => 'mailer@example.com',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $this->assertNotSame('p@ssw0rd-secret', DB::table('mail_settings')->value('password'));
        $this->assertSame('p@ssw0rd-secret', MailSetting::find(1)->password);

        $this->actingAs($admin)->get('/admin')->assertInertia(fn ($page) => $page
            ->where('mail.mailer', 'smtp')
            ->where('mail.has_password', true)
            ->missing('mail.password'));

        MailConfigurator::apply();
        $this->assertSame('smtp', config('mail.default'));
        $this->assertSame('smtp.example.com', config('mail.mailers.smtp.host'));
        $this->assertSame('p@ssw0rd-secret', config('mail.mailers.smtp.password'));
        $this->assertSame('mailer@example.com', config('mail.from.address'));
    }

    public function test_blank_secret_keeps_the_stored_value(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->put('/admin/mail', $this->m365Payload())->assertSessionHasNoErrors();
        $this->actingAs($admin)->put('/admin/mail', $this->m365Payload(['ms_client_secret' => '', 'from_name' => 'SAF']))->assertSessionHasNoErrors();

        $this->assertSame('super-secret-value', MailSetting::find(1)->ms_client_secret);
        $this->assertSame('SAF', MailSetting::find(1)->from_name);
    }

    public function test_microsoft365_requires_its_fields(): void
    {
        $this->actingAs($this->admin())->put('/admin/mail', ['mailer' => 'microsoft365', 'ms_client_id' => 'not-a-guid'])
            ->assertSessionHasErrors(['username', 'ms_tenant_id', 'ms_client_id', 'ms_client_secret', 'from_address']);
    }

    public function test_editors_cannot_see_or_change_mail_settings(): void
    {
        $editor = User::factory()->create(['role' => 'editor']);

        $this->actingAs($editor)->get('/admin')->assertInertia(fn ($page) => $page->where('mail', null));
        $this->actingAs($editor)->put('/admin/mail', $this->m365Payload())->assertForbidden();
        $this->actingAs($editor)->post('/admin/mail/test', ['to' => 'x@example.com'])->assertForbidden();
        $this->assertNull(MailSetting::find(1));
    }

    public function test_microsoft365_transport_uses_an_oauth_token(): void
    {
        Http::fake(['login.microsoftonline.com/*' => Http::response(['access_token' => 'token-abc', 'expires_in' => 3599])]);
        $this->actingAs($this->admin())->put('/admin/mail', $this->m365Payload())->assertSessionHasNoErrors();

        MailConfigurator::apply();
        $transport = Mail::mailer('microsoft365')->getSymfonyTransport();

        $this->assertSame('microsoft365', config('mail.default'));
        $this->assertSame('team@safpartners.ae', config('mail.contact_to'));
        $this->assertInstanceOf(EsmtpTransport::class, $transport);
        $this->assertSame('contact@safpartners.ae', $transport->getUsername());
        $this->assertSame('token-abc', $transport->getPassword());
        Http::assertSent(fn ($request) => str_contains($request->url(), '/safpartners.onmicrosoft.com/oauth2/v2.0/token')
            && $request['grant_type'] === 'client_credentials'
            && $request['scope'] === 'https://outlook.office365.com/.default'
            && $request['client_id'] === self::CLIENT_ID);

        // The token is cached: building the transport again does not call Microsoft again.
        app('mail.manager')->forgetMailers();
        Mail::mailer('microsoft365')->getSymfonyTransport();
        Http::assertSentCount(1);
    }

    public function test_failed_microsoft_sign_in_is_reported_by_the_test_email(): void
    {
        Http::fake(['login.microsoftonline.com/*' => Http::response([
            'error' => 'invalid_client',
            'error_description' => "AADSTS7000215: Invalid client secret provided.\r\nTrace ID: 123",
        ], 401)]);
        $admin = $this->admin();
        $this->actingAs($admin)->put('/admin/mail', $this->m365Payload());

        $this->actingAs($admin)->post('/admin/mail/test', ['to' => 'owner@example.com'])
            ->assertSessionHasErrors(['to' => 'Test email failed: Microsoft 365 sign-in failed: AADSTS7000215: Invalid client secret provided.']);

        $settings = MailSetting::find(1);
        $this->assertSame('failed', $settings->last_test_status);
        $this->assertStringNotContainsString('Trace ID', $settings->last_test_message);
    }

    public function test_test_email_succeeds_with_log_mailer(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->put('/admin/mail', ['mailer' => 'log'])->assertSessionHasNoErrors();

        $this->actingAs($admin)->post('/admin/mail/test', ['to' => 'owner@example.com'])
            ->assertSessionHasNoErrors()->assertSessionHas('success');
        $this->assertSame('ok', MailSetting::find(1)->last_test_status);
    }

    public function test_env_configuration_is_used_until_settings_are_saved(): void
    {
        config(['mail.default' => 'array']);

        MailConfigurator::apply();

        $this->assertSame('array', config('mail.default'));
    }
}
