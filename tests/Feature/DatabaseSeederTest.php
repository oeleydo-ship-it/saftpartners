<?php
namespace Tests\Feature;

use App\Models\Market;
use App\Models\SiteSetting;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_populates_default_content(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(5, Market::count());
        $this->assertNotEmpty(SiteSetting::values()['about_1'] ?? null);
        $this->assertDatabaseHas(User::class, ['email' => 'admin@safpartners.ae', 'role' => 'super_admin']);
    }

    public function test_reseeding_keeps_admin_edits(): void
    {
        $this->seed(DatabaseSeeder::class);

        SiteSetting::where('key', 'about_1')->update(['value' => 'Edited in the CMS']);
        Market::where('slug', 'banks')->first()->delete();
        $admin = User::where('email', 'admin@safpartners.ae')->first();
        $admin->update(['password' => 'a-new-secret-password']);
        $hash = $admin->fresh()->password;

        $this->seed(DatabaseSeeder::class);

        $this->assertSame('Edited in the CMS', SiteSetting::where('key', 'about_1')->value('value'));
        $this->assertSame(4, Market::count());
        $this->assertSame(1, User::count());
        $this->assertSame($hash, $admin->fresh()->password);
    }

    public function test_production_admin_requires_a_password(): void
    {
        $this->app['env'] = 'production';
        $previous = getenv('ADMIN_PASSWORD');
        putenv('ADMIN_PASSWORD=');
        $_ENV['ADMIN_PASSWORD'] = $_SERVER['ADMIN_PASSWORD'] = '';

        try {
            // --force skips the production confirmation prompt, exactly as deploy.sh does.
            $this->artisan('db:seed', ['--force' => true])->assertSuccessful();
        } finally {
            $previous === false ? putenv('ADMIN_PASSWORD') : putenv("ADMIN_PASSWORD={$previous}");
            unset($_ENV['ADMIN_PASSWORD'], $_SERVER['ADMIN_PASSWORD']);
        }

        $this->assertSame(0, User::count());
        $this->assertSame(5, Market::count());
    }
}
