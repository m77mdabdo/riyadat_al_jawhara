<?php

namespace App\Filament\Support;

use Filament\AvatarProviders\Contracts\AvatarProvider;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Initials avatar drawn locally as an SVG data URI, instead of Filament's
 * default ui-avatars.com image (no staff names sent to a third party, no
 * external request on every admin page).
 */
class InitialsAvatarProvider implements AvatarProvider
{
    public function get(Model $record): string
    {
        $name = Str::of(Filament::getNameForDefaultAvatar($record))->trim()->explode(' ')
            ->filter()->take(2)->map(fn (string $part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('');

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 64 64">'
            .'<rect width="64" height="64" fill="#09090b"/>'
            .'<text x="50%" y="50%" dy=".35em" text-anchor="middle" fill="#ffffff" font-family="Inter, Cairo, Arial, sans-serif" font-size="26" font-weight="600">'
            .e($name ?: '?').'</text></svg>';

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}
