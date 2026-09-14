<?php
namespace App\Services\Mail;

use App\Models\MailSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Applies the admin-managed mail settings on top of the .env mail configuration.
 * Until settings are saved in the admin, the MAIL_* environment values are used.
 */
class MailConfigurator
{
    private const CACHE_KEY = 'mail.settings.raw';

    /** Saved settings, or null when none exist or the database is unavailable (e.g. during a build). */
    public static function current(): ?MailSetting
    {
        try {
            // Cache the raw (still encrypted) columns so secrets never sit decrypted in the cache store.
            $raw = Cache::get(self::CACHE_KEY);
            if ($raw === null) {
                if (! Schema::hasTable('mail_settings')) return null;
                $raw = MailSetting::find(1)?->getAttributes() ?? [];
                Cache::forever(self::CACHE_KEY, $raw);
            }

            return $raw ? (new MailSetting)->setRawAttributes($raw, true) : null;
        } catch (Throwable) {
            return null;
        }
    }

    public static function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public static function apply(): void
    {
        $settings = self::current();
        if (! $settings) return;

        config([
            'mail.from.address' => $settings->from_address ?: config('mail.from.address'),
            'mail.from.name' => $settings->from_name ?: config('mail.from.name'),
            'mail.contact_to' => $settings->contact_to ?: config('mail.contact_to'),
        ]);

        match ($settings->mailer) {
            'smtp' => config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp' => array_merge(config('mail.mailers.smtp', []), [
                    'transport' => 'smtp',
                    'url' => null,
                    // "smtps" is implicit TLS (usually port 465); "smtp" upgrades with STARTTLS when offered (587).
                    'scheme' => $settings->encryption === 'ssl' ? 'smtps' : 'smtp',
                    'host' => $settings->host,
                    'port' => $settings->port,
                    'username' => $settings->username,
                    'password' => $settings->password,
                ]),
            ]),
            'microsoft365' => config([
                'mail.default' => 'microsoft365',
                'mail.mailers.microsoft365' => [
                    'transport' => 'microsoft365',
                    'host' => 'smtp.office365.com',
                    'port' => 587,
                    'username' => $settings->username,
                    'tenant_id' => $settings->ms_tenant_id,
                    'client_id' => $settings->ms_client_id,
                    'client_secret' => $settings->ms_client_secret,
                ],
            ]),
            default => config(['mail.default' => 'log']),
        };

        // Drop already-resolved mailers so the new configuration (and a fresh OAuth token) is used.
        if (app()->resolved('mail.manager')) {
            app('mail.manager')->forgetMailers();
        }
    }
}
