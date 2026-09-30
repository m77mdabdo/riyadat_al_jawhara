<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabelOptions;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum LeadStatus: string implements HasColor, HasIcon, HasLabel
{
    use HasLabelOptions;

    case New = 'new';
    case Contacted = 'contacted';
    case InProgress = 'in_progress';
    case Closed = 'closed';

    public function getLabel(?string $locale = null): string
    {
        return __('enums.lead_status.'.$this->value, [], $locale);
    }

    public function getColor(): string
    {
        return match ($this) {
            self::New => 'warning',
            self::Contacted => 'info',
            self::InProgress => 'primary',
            self::Closed => 'success',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::New => 'heroicon-o-sparkles',
            self::Contacted => 'heroicon-o-phone',
            self::InProgress => 'heroicon-o-arrow-path',
            self::Closed => 'heroicon-o-check-circle',
        };
    }

    /** Colour for a stored value (unknown values are neutral). */
    public static function colorFor(?string $value): string
    {
        return self::tryFrom((string) $value)?->getColor() ?? 'gray';
    }
}
