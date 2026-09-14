<?php
namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Mail\ContactSubmissionReceived;
use App\Models\ContactSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function store(ContactRequest $request): RedirectResponse
    {
        $submission = ContactSubmission::create([
            ...$request->safe()->except(['consent', 'website']),
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
        ]);

        if (config('mail.contact_to')) {
            Mail::to(config('mail.contact_to'))->queue(new ContactSubmissionReceived($submission));
        }

        return back()->with('success', 'Thank you. Your message has been sent.');
    }
}
