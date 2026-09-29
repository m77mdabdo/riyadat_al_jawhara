<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeadRequest;
use App\Models\Lead;
use App\Models\Service;
use App\Models\Setting;
use App\Models\StoneType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
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
        $data = $request->validated();

        // Honeypot: bots that fill this hidden field get the normal success
        // response, but nothing is stored, so they can't tell they were caught.
        if (filled($data['website'] ?? null)) {
            return back()->with('success', __('site.contact.success_message'));
        }

        Lead::create(Arr::except($data, 'website') + ['status' => 'new', 'source' => 'website']);

        return back()->with('success', __('site.contact.success_message'));
    }
}
