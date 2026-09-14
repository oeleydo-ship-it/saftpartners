<?php

namespace App\Providers;

use App\Models\User;
use App\Services\Mail\MailConfigurator;
use App\Services\Mail\Microsoft365TransportFactory;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
        Gate::define('manage-content', fn (User $user) => $user->isAdmin());
        Gate::define('delete-content', fn (User $user) => $user->canDeleteContent());
        Gate::define('manage-users', fn (User $user) => $user->role === 'super_admin');
        // Mail settings hold credentials, so editors are excluded.
        Gate::define('manage-mail', fn (User $user) => in_array($user->role, ['super_admin', 'admin'], true));

        RateLimiter::for('contact', function (Request $request) {
            $tooMany = fn () => back()->withErrors(['form' => 'Too many messages. Please try again later.']);
            $email = strtolower(trim((string) $request->input('email')));

            return [
                Limit::perMinute(3)->by('contact-minute:'.$request->ip())->response($tooMany),
                Limit::perDay(20)->by('contact-day:'.$request->ip())->response($tooMany),
                Limit::perHour(5)->by('contact-email:'.sha1($email))->response($tooMany),
            ];
        });

        Mail::extend('microsoft365', fn (array $config) => $this->app->make(Microsoft365TransportFactory::class)->make($config));
        MailConfigurator::apply();
        // Long-running queue workers pick up settings changed in the admin before each job.
        Queue::before(fn () => MailConfigurator::apply());
    }
}
