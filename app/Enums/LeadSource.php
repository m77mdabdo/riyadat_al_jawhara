<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabelOptions;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum LeadSource: string implements HasColor, HasLabel
{
    use HasLabelOptions;

    case Website = 'website';
    case Whatsapp = 'whatsapp';

    public function getLabel(?string $locale = null): string
    {
        return __('enums.lead_source.'.$this->value, [], $locale);
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Website => 'gray',
            self::Whatsapp => 'success',
        };
    }
}
