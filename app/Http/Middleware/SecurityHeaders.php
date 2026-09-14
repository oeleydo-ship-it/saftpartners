<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $nonce = base64_encode(random_bytes(16));
        view()->share('cspNonce', $nonce);
        Vite::useCspNonce($nonce);

        // Allow the Vite dev server (HMR) only while it is running locally.
        $viteDev = app()->isLocal() && Vite::isRunningHot()
            ? rtrim(trim(file_get_contents(Vite::hotFile())), '/')
            : '';

        $response = $next($request);
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $response->headers->set('Content-Security-Policy', "default-src 'self'; img-src 'self' data: {$viteDev}; style-src 'self' 'unsafe-inline' https://fonts.bunny.net {$viteDev}; script-src 'self' 'nonce-{$nonce}' {$viteDev}; font-src 'self' data: https://fonts.bunny.net {$viteDev}; connect-src 'self' ws: wss: {$viteDev}; frame-ancestors 'none'; base-uri 'self'; form-action 'self'");
        if ($request->isSecure()) $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        return $response;
    }
}
