<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

/**
 * /dev/typography: the type scale in Arabic and English side by side, for
 * design review. Only available in the local environment (404 elsewhere).
 */
class TypographyController extends Controller
{
    public function __invoke(): View
    {
        abort_unless(app()->isLocal(), 404);

        return view('dev.typography');
    }
}
