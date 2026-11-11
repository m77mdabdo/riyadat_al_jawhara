<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Admin panel language: the signed-in user's saved preference, else the
 * language chosen on the sign-in page (session), else APP_LOCALE.
 * Registered as persistent panel middleware so Livewire requests follow it too.
 */
class SetAdminLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        // Signed in: the saved preference; on the sign-in pages: the choice made there.
        $locale = $request->user()?->locale ?? ($request->hasSession() ? $request->session()->get('admin_locale') : null);

        if (in_array($locale, config('locales.supported'), true)) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
