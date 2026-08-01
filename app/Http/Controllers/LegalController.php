<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\View\View;

class LegalController extends Controller
{
    public function privacyPolicy(): View
    {
        return view('legal.privacy-policy', [
            'settings' => Setting::current(),
        ]);
    }

    public function termsOfUse(): View
    {
        return view('legal.terms-of-use', [
            'settings' => Setting::current(),
        ]);
    }
}
