<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabelOptions;
use Filament\Support\Contracts\HasLabel;

enum UserRole: string implements HasLabel
{
    use HasLabelOptions;

    case Admin = 'admin';
    case Editor = 'editor';

    public function getLabel(?string $locale = null): string
    {
        return __('enums.user_role.'.$this->value, [], $locale);
    }
}
