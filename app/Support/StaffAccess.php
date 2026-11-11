<?php

namespace App\Support;

use App\Models\User;
use App\Notifications\StaffAccessNotification;
use Illuminate\Support\Facades\Password;
use Throwable;

/**
 * Emails a team member a time-limited link to the admin's "choose a password"
 * page: as an invitation when the account is new, otherwise as a fresh access
 * link. Returns whether the email was handed to the mailer.
 */
class StaffAccess
{
    public static function send(User $user, bool $invitation): bool
    {
        try {
            $token = Password::broker(config('auth.defaults.passwords'))->createToken($user);
            $url = filament()->getPanel('admin')->getResetPasswordUrl($token, $user);

            $user->loadMissing('roles')->notifyNow(new StaffAccessNotification($url, $invitation));

            return true;
        } catch (Throwable $e) {
            report($e);

            return false;
        }
    }
}
