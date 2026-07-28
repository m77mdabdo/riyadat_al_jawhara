<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeadRequest;
use App\Models\Lead;
use App\Models\Service;
use App\Models\Setting;
use App\Models\StoneType;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function index(): View
    {
        return view('contact', [
            'settings' => Setting::current(),
            'services' => Service::active()->ordered()->get(),
            'stoneTypes' => StoneType::active()->ordered()->get(),
        ]);
    }

    public function store(StoreLeadRequest $request): RedirectResponse
    {
        Lead::create($request->validated() + ['status' => 'new', 'source' => 'website']);

        return back()->with('success', __('site.contact.success_message'));
    }
}
