<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MarketRequest;
use App\Http\Requests\Admin\SettingsRequest;
use App\Http\Requests\Admin\TeamMemberRequest;
use App\Models\AuditLog;
use App\Models\ContactSubmission;
use App\Models\Market;
use App\Models\Media;
use App\Models\SiteSetting;
use App\Models\TeamMember;
use App\Services\Audit;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'settings' => SiteSetting::values(),
            'markets' => Market::withTrashed()->orderBy('sort_order')->get(),
            'team' => TeamMember::withTrashed()->orderBy('sort_order')->get(),
            'contacts' => ContactSubmission::latest()->limit(100)->get(),
            'media' => Media::latest()->limit(100)->get(),
            'audit' => AuditLog::latest('id')->limit(20)->get(),
            'stats' => [
                'markets' => Market::count(),
                'team' => TeamMember::count(),
                'newContacts' => ContactSubmission::where('status', 'new')->count(),
                'media' => Media::count(),
            ],
        ]);
    }

    public function settings(SettingsRequest $request): RedirectResponse
    {
        foreach ($request->validated('settings') as $key => $value) {
            SiteSetting::updateOrCreate(['key' => $key], ['value' => $value, 'group' => str($key)->before('_')->toString()]);
        }
        Audit::record('settings.updated', SiteSetting::class, 'Updated public website content');
        return back()->with('success', 'Website content saved.');
    }

    public function marketStore(MarketRequest $request): RedirectResponse
    {
        $market = Market::create($request->validated());
        Audit::record('market.created', $market);
        return back()->with('success', 'Market created.');
    }

    public function marketUpdate(MarketRequest $request, Market $market): RedirectResponse
    {
        $market->update($request->validated());
        Audit::record('market.updated', $market);
        return back()->with('success', 'Market updated.');
    }

    public function marketDestroy(Market $market): RedirectResponse
    {
        abort_unless(request()->user()->canDeleteContent(), 403);
        $market->delete();
        Audit::record('market.deleted', $market);
        return back()->with('success', 'Market archived.');
    }

    public function teamStore(TeamMemberRequest $request): RedirectResponse
    {
        $member = TeamMember::create($request->validated());
        Audit::record('team.created', $member);
        return back()->with('success', 'Team member created.');
    }

    public function teamUpdate(TeamMemberRequest $request, TeamMember $team_member): RedirectResponse
    {
        $team_member->update($request->validated());
        Audit::record('team.updated', $team_member);
        return back()->with('success', 'Team member updated.');
    }

    public function teamDestroy(TeamMember $team_member): RedirectResponse
    {
        abort_unless(request()->user()->canDeleteContent(), 403);
        $team_member->delete();
        Audit::record('team.deleted', $team_member);
        return back()->with('success', 'Team member archived.');
    }

    public function contactStatus(ContactSubmission $contact, string $status): RedirectResponse
    {
        abort_unless(in_array($status, ['new', 'read', 'replied', 'archived', 'spam'], true), 422);
        $contact->update(['status' => $status]);
        Audit::record('contact.status_updated', $contact, "Status changed to {$status}");
        return back()->with('success', 'Contact status updated.');
    }
}
