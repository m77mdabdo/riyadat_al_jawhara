<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Admin panel language switch: saved for the signed-in user (user menu), or
 * in the session on the sign-in pages.
 */
class AdminLocaleController extends Controller
{
    public function __invoke(Request $request, string $language): RedirectResponse
    {
        abort_unless(in_array($language, config('locales.supported'), true), 404);
        // Guests choose on the sign-in page (kept in the session); signed-in
        // users must be panel users and the choice is saved on their account.
        if ($request->user()) {
            abort_unless($request->user()->canAccessPanel(filament()->getPanel('admin')), 403);
            $request->user()->forceFill(['locale' => $language])->save();
        }
        $request->session()->put('admin_locale', $language);

        $previous = url()->previous();

        return redirect()->to(str_starts_with($previous, url('/admin')) ? $previous : url('/admin'));
    }
}
