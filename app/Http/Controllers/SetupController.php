<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

/**
 * First-run wizard: creates the initial super admin account after deployment.
 */
class SetupController extends Controller
{
    private const CACHE_KEY = 'setup.complete';

    public static function isComplete(): bool
    {
        if (Cache::get(self::CACHE_KEY)) return true;

        $complete = User::where('role', 'super_admin')->exists();
        // Only cache the positive result so the wizard disappears as soon as setup finishes.
        if ($complete) Cache::forever(self::CACHE_KEY, true);

        return $complete;
    }

    public function show(): Response|RedirectResponse
    {
        if (self::isComplete()) return redirect('/');

        return Inertia::render('Setup/Install', ['requiresToken' => filled(config('app.setup_token'))]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (self::isComplete()) return redirect('/');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:190', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(10)->letters()->numbers()],
            'setup_token' => [filled(config('app.setup_token')) ? 'required' : 'nullable', 'string'],
        ]);

        $token = (string) config('app.setup_token');
        if ($token !== '' && ! hash_equals($token, (string) $data['setup_token'])) {
            return back()->withErrors(['setup_token' => 'The setup key is incorrect.']);
        }

        $user = DB::transaction(function () use ($data) {
            // Re-check inside the transaction so two simultaneous submissions cannot both succeed.
            abort_if(User::where('role', 'super_admin')->lockForUpdate()->exists(), 409, 'Setup has already been completed.');

            $user = User::create(['name' => $data['name'], 'email' => $data['email'], 'password' => $data['password']]);
            $user->forceFill(['role' => 'super_admin', 'email_verified_at' => now()])->save();

            return $user;
        });

        Cache::forever(self::CACHE_KEY, true);
        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/')->with('success', 'Setup complete. Welcome to SAF Partners.');
    }
}
