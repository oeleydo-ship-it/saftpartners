<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MailSettingsRequest;
use App\Models\MailSetting;
use App\Services\Audit;
use App\Services\Mail\MailConfigurator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class MailSettingsController extends Controller
{
    public function update(MailSettingsRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Blank secret fields keep the stored values.
        foreach (['password', 'ms_client_secret'] as $secret) {
            if (blank($data[$secret] ?? null)) unset($data[$secret]);
        }

        $settings = MailSetting::current();
        $settings->fill($data)->save();

        MailConfigurator::forget();
        Audit::record('mail.settings_updated', $settings, "Outgoing email set to {$settings->mailer}");

        return back()->with('success', 'Email settings saved. Send a test email to confirm they work.');
    }

    public function test(Request $request): RedirectResponse
    {
        $to = $request->validate(['to' => ['required', 'email:rfc', 'max:190']])['to'];

        MailConfigurator::forget();
        MailConfigurator::apply();
        $settings = MailSetting::find(1);

        try {
            Mail::raw(
                "This is a test email from the SAF Partners website.\n\nIf you received it, outgoing email is configured correctly.",
                fn ($message) => $message->to($to)->subject('SAF Partners test email'),
            );
        } catch (Throwable $e) {
            $reason = mb_substr(strtok($e->getMessage(), "\r\n") ?: 'Unknown error', 0, 500);
            Log::warning('Test email failed', ['mailer' => config('mail.default'), 'error' => $reason]);
            $settings?->forceFill(['last_tested_at' => now(), 'last_test_status' => 'failed', 'last_test_message' => $reason])->save();
            MailConfigurator::forget();

            return back()->withErrors(['to' => "Test email failed: {$reason}"]);
        }

        $settings?->forceFill(['last_tested_at' => now(), 'last_test_status' => 'ok', 'last_test_message' => "Sent to {$to}"])->save();
        MailConfigurator::forget();

        return back()->with('success', config('mail.default') === 'log'
            ? 'Email is set to "Log only", so the test email was written to the application log instead of being delivered.'
            : "Test email sent to {$to}. Check the inbox (and junk folder).");
    }
}
