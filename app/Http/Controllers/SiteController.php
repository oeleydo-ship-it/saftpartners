<?php
namespace App\Http\Controllers;

use App\Models\Market;
use App\Models\SiteSetting;
use App\Models\TeamMember;
use Inertia\Inertia;
use Inertia\Response;

class SiteController extends Controller
{
    public function home(): Response
    {
        return Inertia::render('Public/Home', [
            'settings' => SiteSetting::values(),
            'markets' => Market::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'team' => TeamMember::query()->where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function page(string $page): Response
    {
        abort_unless(in_array($page, ['about', 'markets', 'team', 'contact', 'privacy-policy', 'terms'], true), 404);
        return Inertia::render('Public/Page', ['page' => $page, 'settings' => SiteSetting::values()]);
    }
}
