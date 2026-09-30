<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Saves the admin panel language for the signed-in user (user-menu switch).
 */
class AdminLocaleController extends Controller
{
    public function __invoke(Request $request, string $language): RedirectResponse
    {
        abort_unless(in_array($language, config('locales.supported'), true), 404);
        abort_unless($request->user()?->canAccessPanel(filament()->getPanel('admin')), 403);

        $request->user()->forceFill(['locale' => $language])->save();

        $previous = url()->previous();

        return redirect()->to(str_starts_with($previous, url('/admin')) ? $previous : url('/admin'));
    }
}
