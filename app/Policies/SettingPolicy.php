<?php

namespace App\Policies;

use App\Models\User;

/**
 * Settings, branding and email previews: admins, or the settings.manage permission.
 */
class SettingPolicy
{
    public function update(User $user): bool
    {
        return $user->hasPermission('settings.manage');
    }
}
