<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabelOptions;
use Filament\Support\Contracts\HasLabel;

enum StoneOriginType: string implements HasLabel
{
    use HasLabelOptions;

    case Local = 'local';
    case Imported = 'imported';

    public function getLabel(?string $locale = null): string
    {
        return __('site.common.'.$this->value, [], $locale);
    }
}
