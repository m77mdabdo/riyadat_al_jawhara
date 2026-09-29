<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Project;
use App\Models\WhatsappClick;
use App\Support\LocalizedUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Beacon endpoint hit (navigator.sendBeacon) when a visitor clicks any
 * WhatsApp button. Always records an anonymous event. Creates a lead
 * (source=whatsapp) only when the page context is known -- a service, stone
 * or project -- and at most once per visitor session per context, so repeated
 * clicks don't flood the lead list.
 */
class WhatsappClickController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $data = $request->validate([
            'page' => ['nullable', 'string', 'max:2048'],
            'locale' => ['nullable', Rule::in(LocalizedUrl::supported())],
            'context_type' => ['nullable', Rule::in(array_keys(WhatsappClick::CONTEXTS))],
            'context_id' => ['nullable', 'integer', 'min:1'],
        ]);

        // Keep only the path + query of our own pages (never a full external URL).
        $page = isset($data['page']) ? Str::limit(parse_url($data['page'], PHP_URL_PATH).(($q = parse_url($data['page'], PHP_URL_QUERY)) ? '?'.$q : ''), 2000, '') : null;

        $context = null;
        if (! empty($data['context_type']) && ! empty($data['context_id'])) {
            $class = WhatsappClick::CONTEXTS[$data['context_type']];
            $context = $class::query()->where('is_active', true)->find($data['context_id']);
        }

        $lead = $context ? $this->leadFor($request, $data['context_type'], $context, $data['locale'] ?? null, $page) : null;

        WhatsappClick::create([
            'page' => $page,
            'locale' => $data['locale'] ?? null,
            'context_type' => $context ? $data['context_type'] : null,
            'context_id' => $context?->getKey(),
            'lead_id' => $lead?->id,
        ]);

        return response()->noContent();
    }

    private function leadFor(Request $request, string $type, $context, ?string $locale, ?string $page): ?Lead
    {
        $sessionKey = "whatsapp_lead.{$type}.{$context->getKey()}";

        if ($request->session()->has($sessionKey)) {
            return Lead::find($request->session()->get($sessionKey));
        }

        $label = $type === 'project' ? $context->title_en : $context->name_en;

        $lead = Lead::create([
            'name' => 'WhatsApp visitor',
            'phone' => '',
            'message' => "Opened WhatsApp from the {$type} page: {$label}. Match this lead with the incoming WhatsApp chat.",
            'status' => 'new',
            'source' => 'whatsapp',
            'locale' => $locale,
            'source_url' => $page ? url($page) : null,
            'service_id' => $type === 'service' ? $context->getKey() : ($context instanceof Project ? $context->service_id : null),
            'stone_type_id' => $type === 'stone_type' ? $context->getKey() : ($context instanceof Project ? $context->stone_type_id : null),
            'project_id' => $type === 'project' ? $context->getKey() : null,
        ]);

        $request->session()->put($sessionKey, $lead->id);

        return $lead;
    }
}
