<?php
namespace App\Services;

use App\Models\ContactSubmission;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

/**
 * Lightweight, privacy-friendly spam checks for the public contact form
 * (no third-party CAPTCHA, no cookies beyond the session).
 */
class SpamGuard
{
    /** Humans need at least this long to fill in the form. */
    public const MIN_SECONDS = 3;

    /** Tokens older than this are rejected with a friendly "please resend" message. */
    public const MAX_AGE_SECONDS = 86400;

    public const MAX_LINKS = 2;

    /** Reasons that are silently discarded (the sender still sees a success message). */
    public const DROP = ['honeypot', 'invalid_token', 'too_fast', 'duplicate'];

    /** Reasons that are stored with status "spam" for admin review, without notifications. */
    public const QUARANTINE = ['links', 'markup'];

    /** Encrypted render timestamp embedded in the form. */
    public static function token(): string
    {
        return Crypt::encryptString((string) now()->getTimestamp());
    }

    /**
     * Returns null for a clean submission, or one of: honeypot, invalid_token, too_fast,
     * expired, duplicate, links, markup.
     */
    public function inspect(Request $request): ?string
    {
        if (filled($request->input('website'))) return 'honeypot';

        try {
            $renderedAt = (int) Crypt::decryptString((string) $request->input('form_token'));
        } catch (DecryptException) {
            return 'invalid_token';
        }

        $elapsed = now()->getTimestamp() - $renderedAt;
        if ($elapsed < self::MIN_SECONDS) return 'too_fast';
        if ($elapsed > self::MAX_AGE_SECONDS) return 'expired';

        $text = implode(' ', [$request->input('name'), $request->input('company'), $request->input('subject'), $request->input('message')]);
        if (preg_match('/<a\s|\[url[=\]]|\[link[=\]]/i', $text)) return 'markup';
        if (preg_match('#https?://|www\.#i', (string) $request->input('name'))) return 'links';
        if (preg_match_all('#https?://|www\.#i', $text) > self::MAX_LINKS) return 'links';

        $duplicate = ContactSubmission::withTrashed()
            ->where('email', $request->input('email'))
            ->where('message', $request->input('message'))
            ->where('created_at', '>=', now()->subDay())
            ->exists();

        return $duplicate ? 'duplicate' : null;
    }
}
