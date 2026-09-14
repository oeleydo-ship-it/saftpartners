<?php
namespace App\Http\Controllers;

use App\Models\Market;
use App\Models\SiteSetting;
use App\Models\TeamMember;
use App\Services\SpamGuard;
use App\Support\DefaultContent;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class SiteController extends Controller
{
    public function home(): Response
    {
        return Inertia::render('Public/Home', [
            'settings' => SiteSetting::values(),
            'markets' => $this->markets(),
            'team' => TeamMember::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'formToken' => SpamGuard::token(),
        ]);
    }

    public function page(string $page): Response
    {
        abort_unless(in_array($page, ['about', 'markets', 'team', 'contact', 'privacy-policy', 'terms'], true), 404);
        return Inertia::render('Public/Page', ['page' => $page, 'settings' => SiteSetting::values()]);
    }

    /** Active markets, or the launch defaults if the markets table has never been populated. */
    private function markets(): Collection
    {
        if (Market::withTrashed()->exists()) {
            return Market::query()->where('is_active', true)->orderBy('sort_order')->get();
        }

        return collect(DefaultContent::markets())->map(fn (array $market, int $i) => ['id' => -($i + 1), ...$market]);
    }
}
