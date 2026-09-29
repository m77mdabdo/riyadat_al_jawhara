<?php

namespace App\Http\Middleware;

use App\Support\LocalizedUrl;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sets the public-site language from the URL prefix (/ar/..., /en/...).
 *
 * - Only acts on locale-prefixed requests. Everything else (admin, Livewire
 *   updates, sitemap, robots) keeps the app default, so the admin panel is
 *   unaffected.
 * - Never guesses from Accept-Language or cookies: the URL is the only source.
 * - Runs before route-model binding (see bootstrap/app.php) so a 404 for an
 *   unknown slug already renders in the right language.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->segment(1);

        if (LocalizedUrl::isSupported($locale)) {
            app()->setLocale($locale);
            URL::defaults(['locale' => $locale]);

            // Controllers don't take $locale; drop it so positional arguments
            // (e.g. show(Project $project)) line up.
            $request->route()?->forgetParameter('locale');
        }

        return $next($request);
    }
}
