<?php
namespace App\Services\Mail;

use InvalidArgumentException;
use Symfony\Component\Mailer\Transport\Smtp\Auth\XOAuth2Authenticator;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;

/**
 * SMTP transport for Exchange Online (smtp.office365.com:587, STARTTLS) authenticating with XOAUTH2.
 */
class Microsoft365TransportFactory
{
    public function __construct(private Microsoft365TokenProvider $tokens) {}

    public function make(array $config): EsmtpTransport
    {
        foreach (['username', 'tenant_id', 'client_id', 'client_secret'] as $key) {
            if (blank($config[$key] ?? null)) {
                throw new InvalidArgumentException("Microsoft 365 mail is missing its {$key} setting.");
            }
        }

        // tls=false + auto TLS: connect on 587 and upgrade with STARTTLS, as Exchange Online requires.
        $transport = new EsmtpTransport($config['host'] ?? 'smtp.office365.com', (int) ($config['port'] ?? 587), false);
        $transport->setUsername($config['username']);
        $transport->setPassword($this->tokens->token($config['tenant_id'], $config['client_id'], $config['client_secret']));
        // Only XOAUTH2: never fall back to sending the token as a LOGIN/PLAIN password.
        $transport->setAuthenticators([new XOAuth2Authenticator()]);

        if ($host = parse_url((string) config('app.url'), PHP_URL_HOST)) {
            $transport->setLocalDomain($host);
        }

        return $transport;
    }
}
