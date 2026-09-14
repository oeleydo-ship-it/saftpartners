<?php
namespace App\Services\Mail;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Obtains OAuth 2.0 access tokens for Exchange Online SMTP using the client credentials flow
 * (Microsoft Entra app registration with the SMTP.SendAsApp application permission).
 */
class Microsoft365TokenProvider
{
    public const SCOPE = 'https://outlook.office365.com/.default';

    public function token(string $tenantId, string $clientId, string $clientSecret): string
    {
        $cacheKey = 'mail.m365.token.'.sha1("{$tenantId}|{$clientId}|{$clientSecret}");
        if ($cached = Cache::get($cacheKey)) {
            return Crypt::decryptString($cached);
        }

        $response = Http::asForm()->timeout(15)->post(
            'https://login.microsoftonline.com/'.rawurlencode($tenantId).'/oauth2/v2.0/token',
            ['grant_type' => 'client_credentials', 'client_id' => $clientId, 'client_secret' => $clientSecret, 'scope' => self::SCOPE],
        );

        $token = $response->json('access_token');
        if ($response->failed() || ! is_string($token) || $token === '') {
            // Microsoft error descriptions include trace/correlation lines; the first line is the useful part.
            $reason = strtok((string) ($response->json('error_description') ?? "HTTP {$response->status()}"), "\r\n");
            throw new RuntimeException("Microsoft 365 sign-in failed: {$reason}");
        }

        // Refresh five minutes before expiry; stored encrypted.
        Cache::put($cacheKey, Crypt::encryptString($token), max(60, (int) $response->json('expires_in', 3600) - 300));

        return $token;
    }
}
