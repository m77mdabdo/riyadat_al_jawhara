<?php

namespace App\Actions\Leads;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Enums\WhatsappContext;
use App\Models\Lead;
use App\Models\Project;
use App\Models\WhatsappClick;
use Illuminate\Contracts\Session\Session;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Records a click on a WhatsApp button. Always stores an anonymous event.
 * Creates a lead (source=whatsapp) only when the page context is known -- a
 * service, stone or project -- and at most once per visitor session per
 * context, so repeated clicks don't flood the lead list, and at most
 * site.whatsapp_daily_lead_cap a day in total (deleted ones included).
 */
class RecordWhatsappClick
{
    /**
     * @param  array{page?: ?string, locale?: ?string, context_type?: ?string, context_id?: int|string|null}  $data
     */
    public function handle(array $data, Session $session): WhatsappClick
    {
        // Keep only the path + query of our own pages (never a full external URL).
        $page = isset($data['page']) ? Str::limit(parse_url($data['page'], PHP_URL_PATH).(($q = parse_url($data['page'], PHP_URL_QUERY)) ? '?'.$q : ''), 2000, '') : null;

        $type = ! empty($data['context_type']) ? WhatsappContext::from($data['context_type']) : null;
        $context = $type && ! empty($data['context_id'])
            ? $type->modelClass()::query()->where('is_active', true)->find($data['context_id'])
            : null;

        $lead = $type && $context ? $this->leadFor($session, $type, $context, $data['locale'] ?? null, $page) : null;

        return WhatsappClick::create([
            'page' => $page,
            'locale' => $data['locale'] ?? null,
            'context_type' => $context ? $type->value : null,
            'context_id' => $context?->getKey(),
            'lead_id' => $lead?->id,
        ]);
    }

    private function leadFor(Session $session, WhatsappContext $type, Model $context, ?string $locale, ?string $page): ?Lead
    {
        $sessionKey = "whatsapp_lead.{$type->value}.{$context->getKey()}";

        if ($session->has($sessionKey)) {
            return Lead::find($session->get($sessionKey));
        }

        if ($this->dailyCapReached()) {
            return null;
        }

        $label = $context->getAttribute($type === WhatsappContext::Project ? 'title_en' : 'name_en');

        $lead = Lead::create([
            'name' => 'WhatsApp visitor',
            'phone' => '',
            'message' => "Opened WhatsApp from the {$type->value} page: {$label}. Match this lead with the incoming WhatsApp chat.",
            'status' => LeadStatus::New->value,
            'source' => LeadSource::Whatsapp->value,
            'locale' => $locale,
            'source_url' => $page ? url($page) : null,
            'service_id' => $type === WhatsappContext::Service ? $context->getKey() : ($context instanceof Project ? $context->service_id : null),
            'stone_type_id' => $type === WhatsappContext::StoneType ? $context->getKey() : ($context instanceof Project ? $context->stone_type_id : null),
            'project_id' => $type === WhatsappContext::Project ? $context->getKey() : null,
        ]);

        $session->put($sessionKey, $lead->id);

        return $lead;
    }

    private function dailyCapReached(): bool
    {
        return Lead::withTrashed()
            ->where('source', LeadSource::Whatsapp->value)
            ->where('created_at', '>=', now()->startOfDay())
            ->count() >= (int) config('site.whatsapp_daily_lead_cap');
    }
}
