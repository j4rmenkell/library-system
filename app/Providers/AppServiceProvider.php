<?php

namespace App\Providers;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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
        $this->configureDefaults();
        $this->configureEmailVerification();
        $this->configureRateLimiting();
    }

    /**
     * One limit for every page and form ("throttle:web" in bootstrap/app.php).
     * Login, two-factor and passkeys also have Fortify's own limits.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('web', function (Request $request): Limit {
            $who = $request->user()->id ?? $request->ip();

            // Each of these sends an email, so they get the tightest limits
            if ($request->routeIs('register.store')) {
                return Limit::perMinute(10)->by('register:'.$request->ip());
            }
            if ($request->routeIs('password.email')) {
                return Limit::perMinute(5)->by('reset-link:'.$request->ip());
            }

            // Saving or deleting anything: far more than a person clicks
            if (! $request->isMethodSafe()) {
                return Limit::perMinute(30)->by('write:'.$who);
            }

            // Page views and searches. Guests share one IP on campus Wi-Fi,
            // so they get more room than a signed-in person
            return $request->user()
                ? Limit::perMinute(120)->by('read:'.$who)
                : Limit::perMinute(300)->by('read:'.$who);
        });
    }

    /**
     * Verification emails link to our own route, which works while logged out
     * (Fortify's needs a signed-in user, and unverified readers can't sign in).
     */
    protected function configureEmailVerification(): void
    {
        VerifyEmail::createUrlUsing(fn (User $user): string => URL::temporarySignedRoute(
            'verification.confirm',
            now()->addMinutes(config('auth.verification.expire', 60)),
            ['id' => $user->getKey(), 'hash' => sha1($user->getEmailForVerification())],
        ));
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
