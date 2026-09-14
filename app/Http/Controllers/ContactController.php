<?php
namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Mail\ContactSubmissionReceived;
use App\Models\ContactSubmission;
use App\Services\SpamGuard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    private const SUCCESS = 'Thank you. Your message has been sent.';

    public function store(ContactRequest $request, SpamGuard $guard): RedirectResponse
    {
        $reason = $guard->inspect($request);

        if ($reason === 'expired') {
            return back()->withErrors(['form_token' => 'This form has expired. Please send your message again.']);
        }

        if ($reason !== null) {
            Log::info('Contact form spam detected', ['reason' => $reason, 'ip' => $request->ip()]);
        }

        // Bots get the same response as humans so they learn nothing from it.
        if (in_array($reason, SpamGuard::DROP, true)) {
            return back()->with('success', self::SUCCESS);
        }

        $submission = ContactSubmission::create([
            ...$request->safe()->except(['consent', 'website', 'form_token']),
            'status' => $reason === null ? 'new' : 'spam',
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
        ]);

        if ($reason === null && config('mail.contact_to')) {
            Mail::to(config('mail.contact_to'))->queue(new ContactSubmissionReceived($submission));
        }

        return back()->with('success', self::SUCCESS);
    }
}
