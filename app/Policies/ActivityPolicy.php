<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Activitylog\Models\Activity;

/**
 * The audit log: admins, or the activity.view permission.
 */
class ActivityPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('activity.view');
    }

    public function view(User $user, Activity $activity): bool
    {
        return $user->hasPermission('activity.view');
    }
}
