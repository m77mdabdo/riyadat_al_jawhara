<?php

namespace App\Http\Controllers;

use App\Actions\Leads\CreateLead;
use App\Http\Requests\StoreLeadRequest;
use App\Models\Project;
use App\Models\Service;
use App\Models\Setting;
use App\Models\StoneType;
use App\Support\VisitorContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            'selectedServiceId' => $service->id ?? $project?->service_id,
            'selectedStoneTypeId' => $stoneType->id ?? $project?->stone_type_id,
        ]);
    }

    public function store(StoreLeadRequest $request, CreateLead $createLead): RedirectResponse
    {
        $lead = $createLead->handle($request->validated(), VisitorContext::fromRequest($request));

        // A caught bot (honeypot) sees the same page, without the conversion flag.
        return $lead ? $this->thankYou()->with('lead_submitted', true) : $this->thankYou();
    }

    /**
     * Confirmation page after a submission (also the analytics conversion
     * target). Reachable directly, but noindex and only fires the conversion
     * event right after a real submission.
     */
    public function thanks(): View
    {
        return view('contact-thanks', [
            'settings' => Setting::current(),
            'justSubmitted' => (bool) session('lead_submitted'),
        ]);
    }

    private function thankYou(): RedirectResponse
    {
        return redirect()->route('contact.thanks')->with('success', __('site.contact.success_message'));
    }
}
