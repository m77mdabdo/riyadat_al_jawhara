<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Activitylog\Models\Activity;

/** The audit trail: admins only (granted through Gate::before). */
class ActivityPolicy
{
    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, Activity $activity): bool
    {
        return false;
    }
}
