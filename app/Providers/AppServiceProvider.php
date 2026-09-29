<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
        // One password policy for every place a password is set: profile page,
        // password reset, and app:create-admin.
        Password::defaults(fn () => Password::min(12)->letters()->numbers());

        // Public lead form: 5 submissions per minute per IP. A throttled visitor
        // is sent back to the form with their input intact and a translated
        // message instead of the bare 429 error page.
        RateLimiter::for('contact', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->ip())
                ->response(fn () => back()
                    ->withInput($request->except('website'))
                    ->with('error', __('site.contact.throttled')));
        });
    }
}
