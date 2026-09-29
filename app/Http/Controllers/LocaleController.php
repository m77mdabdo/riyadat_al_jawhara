<?php

namespace App\Http\Controllers;

use App\Support\LocalizedUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Legacy /lang/{locale} links (from before URL-based languages). Sends the
 * visitor to the same page in the requested language when the referring page
 * is on this site, otherwise to that language's home page.
 */
class LocaleController extends Controller
{
    public function switch(Request $request, string $locale): RedirectResponse
    {
        if (! LocalizedUrl::isSupported($locale)) {
            $locale = LocalizedUrl::default();
        }

        $referer = (string) $request->headers->get('referer');
        $parts = parse_url($referer);

        if ($referer !== '' && ($parts['host'] ?? null) === $request->getHost()) {
            $segments = array_values(array_filter(explode('/', $parts['path'] ?? '')));

            if (LocalizedUrl::isSupported($segments[0] ?? null)) {
                array_shift($segments);
            }

            $path = '/'.$locale.($segments ? '/'.implode('/', $segments) : '');

            return redirect($path.(isset($parts['query']) ? '?'.$parts['query'] : ''));
        }

        return redirect('/'.$locale);
    }
}
