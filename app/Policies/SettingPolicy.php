<?php

namespace App\Policies;

use App\Models\User;

/** Site settings (contact details, legal texts, branding): admins only. */
class SettingPolicy
{
    public function update(User $user): bool
    {
        return false;
    }
}
