<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * An account flagged "new password required" (Administration → Users) can
 * only open its profile page until the password has been changed there.
 */
class RequirePasswordChange
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $profile = Filament::getProfileUrl();

        if ($user instanceof User && $user->password_change_required && $profile
            && $request->isMethod('GET') && ! $request->is('livewire/*')
            && $request->url() !== $profile && ! $request->routeIs('filament.admin.auth.logout')) {
            Notification::make()
                ->title(__('Please choose a new password to continue.'))
                ->warning()
                ->send();

            return redirect($profile);
        }

        return $next($request);
    }
}
