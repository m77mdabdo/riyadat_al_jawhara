<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeadRequest;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Service;
use App\Models\Setting;
use App\Models\StoneType;
use App\Notifications\NewLeadNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function index(Request $request): View
    {
        $services = Service::active()->ordered()->get();
        $stoneTypes = StoneType::active()->ordered()->get();

        // "Request a quote" buttons link here with ?service=, ?stone_type= or
        // ?project= (slugs) so the form opens pre-filled with what the visitor
        // was looking at. Unknown or inactive slugs are simply ignored.
        $project = $request->filled('project')
            ? Project::active()->where('slug', $request->string('project'))->first()
            : null;

        $service = $request->filled('service')
            ? $services->firstWhere('slug', $request->string('service')->toString())
            : null;

        $stoneType = $request->filled('stone_type')
            ? $stoneTypes->firstWhere('slug', $request->string('stone_type')->toString())
            : null;

        return view('contact', [
            'settings' => Setting::current(),
            'services' => $services,
            'stoneTypes' => $stoneTypes,
            'selectedProject' => $project,
            'selectedServiceId' => $service?->id ?? $project?->service_id,
            'selectedStoneTypeId' => $stoneType?->id ?? $project?->stone_type_id,
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

        $lead = Lead::create(Arr::except($data, ['website', 'consent']) + [
            'status' => 'new',
            'source' => 'website',
            'locale' => app()->getLocale(),
            'source_url' => Str::limit((string) $request->headers->get('referer'), 2040, ''),
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 500, ''),
            'consented_at' => now(),
        ]);

        NewLeadNotification::dispatchFor($lead);

        return back()->with('success', __('site.contact.success_message'));
    }
}
