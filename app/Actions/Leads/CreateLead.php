<?php

namespace App\Actions\Leads;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Notifications\NewLeadNotification;
use App\Support\VisitorContext;
use Illuminate\Support\Arr;

/**
 * Stores a quote request from the public contact form and notifies the team.
 */
class CreateLead
{
    /**
     * @param  array<string, mixed>  $data  Validated StoreLeadRequest data
     * @return Lead|null Null when the honeypot was filled (nothing is stored)
     */
    public function handle(array $data, VisitorContext $visitor): ?Lead
    {
        // Honeypot: bots that fill this hidden field get the normal success
        // response, but nothing is stored, so they can't tell they were caught.
        if (filled($data['website'] ?? null)) {
            return null;
        }

        $lead = Lead::create(Arr::except($data, ['website', 'consent']) + [
            'status' => LeadStatus::New->value,
            'source' => LeadSource::Website->value,
            'locale' => $visitor->locale,
            'source_url' => $visitor->referer,
            'ip_address' => $visitor->ip,
            'user_agent' => $visitor->userAgent,
            'consented_at' => now(),
        ]);

        NewLeadNotification::dispatchFor($lead);

        return $lead;
    }
}
