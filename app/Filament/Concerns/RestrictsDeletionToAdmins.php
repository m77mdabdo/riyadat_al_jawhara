<?php

namespace App\Filament\Concerns;

use Illuminate\Database\Eloquent\Model;

trait RestrictsDeletionToAdmins
{
    public static function canDeleteAny(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }
}
