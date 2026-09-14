<?php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SetupWizardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.setup_wizard' => true, 'app.setup_token' => null]);
        Cache::flush();
    }

    private function adminPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Site Owner', 'email' => 'owner@example.com',
            'password' => 'Str0ngPassword1', 'password_confirmation' => 'Str0ngPassword1',
        ], $overrides);
    }

    public function test_site_redirects_to_setup_until_a_super_admin_exists(): void
    {
        $this->get('/')->assertRedirect('/setup');
        $this->get('/login')->assertRedirect('/setup');
        $this->get('/setup')->assertOk()->assertInertia(fn ($page) => $page->component('Setup/Install')->where('requiresToken', false));
    }

    public function test_setup_creates_a_super_admin_and_opens_the_site(): void
    {
        $this->post('/setup', $this->adminPayload())->assertRedirect('/');

        $user = User::where('email', 'owner@example.com')->first();
        $this->assertSame('super_admin', $user->role);
        $this->assertNotNull($user->email_verified_at);
        $this->assertAuthenticatedAs($user);

        $this->get('/')->assertOk();
        $this->get('/setup')->assertRedirect('/');
    }

    public function test_setup_cannot_be_repeated(): void
    {
        $this->post('/setup', $this->adminPayload())->assertRedirect('/');
        $this->post('/setup', $this->adminPayload(['email' => 'attacker@example.com']))->assertRedirect('/');

        $this->assertSame(1, User::count());
    }

    public function test_setup_validates_input(): void
    {
        $this->post('/setup', $this->adminPayload(['email' => 'bad', 'password_confirmation' => 'different', 'password' => 'short']))
            ->assertSessionHasErrors(['email', 'password']);
        $this->assertSame(0, User::count());
    }

    public function test_setup_key_is_required_when_configured(): void
    {
        config(['app.setup_token' => 'correct-horse-battery']);

        $this->get('/setup')->assertInertia(fn ($page) => $page->where('requiresToken', true));
        $this->post('/setup', $this->adminPayload())->assertSessionHasErrors('setup_token');
        $this->post('/setup', $this->adminPayload(['setup_token' => 'wrong']))->assertSessionHasErrors('setup_token');
        $this->assertSame(0, User::count());

        $this->post('/setup', $this->adminPayload(['setup_token' => 'correct-horse-battery']))->assertRedirect('/');
        $this->assertSame(1, User::count());
    }

    public function test_existing_super_admin_skips_the_wizard(): void
    {
        User::factory()->create(['role' => 'super_admin']);

        $this->get('/')->assertOk();
    }
}
