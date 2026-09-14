<?php
namespace App\Http\Middleware;

use App\Http\Controllers\SetupController;
use Closure;
use Illuminate\Http\Request;

/**
 * After a fresh deployment, send every visitor to the setup wizard until a super admin exists.
 */
class EnsureSetupComplete
{
    public function handle(Request $request, Closure $next)
    {
        if (! config('app.setup_wizard') || $request->routeIs('setup.*') || SetupController::isComplete()) {
            return $next($request);
        }

        return redirect()->route('setup.show');
    }
}
