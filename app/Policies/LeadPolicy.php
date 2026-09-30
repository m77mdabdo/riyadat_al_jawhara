<?php

namespace App\Policies;

use App\Models\User;

class LeadPolicy extends ContentPolicy
{
    /** CSV export of all leads (personal data): admins only. */
    public function export(User $user): bool
    {
        return false;
    }
}
