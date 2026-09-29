<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

/**
 * Baseline security headers for the public site and the admin panel.
 *
 * The CSP still allows 'unsafe-inline' / 'unsafe-eval' for scripts because
 * Alpine.js (standard build) and Filament/Livewire evaluate inline
 * expressions. Its value is in everything else: no plugins, no <base>
 * hijacking, forms may only post to this site, only this site may frame it,
 * and scripts/styles/fonts/frames are limited to known origins.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $headers = [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), payment=(), usb=()',
        ];

        // The Vite dev server (npm run dev) injects scripts from another
        // origin; only send a CSP for built assets.
        if (! Vite::isRunningHot()) {
            $headers['Content-Security-Policy'] = self::contentSecurityPolicy();
        }

        // HSTS only over HTTPS in production, so local HTTP keeps working.
        if ($request->isSecure() && app()->isProduction()) {
            $headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains';
        }

        foreach ($headers as $name => $value) {
            if (! $response->headers->has($name)) {
                $response->headers->set($name, $value);
            }
        }

        return $response;
    }

    public static function contentSecurityPolicy(): string
    {
        return implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval'",
            // Public fonts are self-hosted; Bunny Fonts is only used by the Filament admin.
            "style-src 'self' 'unsafe-inline' https://fonts.bunny.net",
            "font-src 'self' data: https://fonts.bunny.net",
            "img-src 'self' data: blob: https:",
            "media-src 'self'",
            // Google Maps embed on the contact page.
            'frame-src https://maps.google.com https://www.google.com',
            "connect-src 'self'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'self'",
        ]);
    }
}
