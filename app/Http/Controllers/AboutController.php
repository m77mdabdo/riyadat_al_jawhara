<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\TeamMember;
use Illuminate\View\View;

class AboutController extends Controller
{
    public function index(): View
    {
        return view('about', [
            'settings' => Setting::current(),
            'teamMembers' => TeamMember::active()->ordered()->get(),
        ]);
    }
}
