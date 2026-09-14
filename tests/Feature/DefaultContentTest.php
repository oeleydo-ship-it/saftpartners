<?php
namespace Tests\Feature;

use App\Models\Market;
use App\Models\SiteSetting;
use App\Support\DefaultContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DefaultContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_migrations_seed_default_content(): void
    {
        $this->assertSame(count(DefaultContent::settings()), SiteSetting::count());
        $this->assertSame(count(DefaultContent::markets()), Market::count());
    }

    public function test_home_page_shows_default_content_on_an_empty_database(): void
    {
        SiteSetting::query()->delete();
        Market::query()->forceDelete();

        $this->get('/')->assertOk()->assertInertia(fn ($page) => $page
            ->where('settings.about_1', DefaultContent::settings()['about_1'])
            ->where('settings.contact_address', DefaultContent::settings()['contact_address'])
            ->has('markets', count(DefaultContent::markets())));
    }

    public function test_cms_edits_win_over_defaults(): void
    {
        SiteSetting::where('key', 'about_1')->update(['value' => 'Edited in the CMS']);
        Market::where('slug', 'banks')->first()->delete();

        $this->get('/')->assertInertia(fn ($page) => $page
            ->where('settings.about_1', 'Edited in the CMS')
            ->has('markets', count(DefaultContent::markets()) - 1));
    }
}
