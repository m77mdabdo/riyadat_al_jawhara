<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabelOptions;
use Filament\Support\Contracts\HasLabel;

/** Specification groups for stones, in display order. */
enum StoneAttributeType: string implements HasLabel
{
    use HasLabelOptions;

    case Finish = 'finish';
    case Colour = 'colour';
    case Size = 'size';
    case Application = 'application';

    public function getLabel(?string $locale = null): string
    {
        return __('site.stones.'.$this->value, [], $locale);
    }
}
