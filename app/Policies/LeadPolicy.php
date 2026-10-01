<?php

namespace App\Policies;

use App\Models\User;

class LeadPolicy extends ContentPolicy
{
    /** CSV export of all leads (personal data): admins, or the leads.export permission. */
    public function export(User $user): bool
    {
        return $this->allows($user, 'export', false);
    }
}
