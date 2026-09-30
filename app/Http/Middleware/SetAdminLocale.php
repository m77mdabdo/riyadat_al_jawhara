<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Admin panel language: the signed-in user's saved preference, else APP_LOCALE.
 * Registered as persistent panel middleware so Livewire requests follow it too.
 */
class SetAdminLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->user()?->locale;

        if (in_array($locale, config('locales.supported'), true)) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
