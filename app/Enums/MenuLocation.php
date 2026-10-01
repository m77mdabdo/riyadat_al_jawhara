<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabelOptions;
use Filament\Support\Contracts\HasLabel;

/** Where an editable menu is shown on the public site. */
enum MenuLocation: string implements HasLabel
{
    use HasLabelOptions;

    case Header = 'header';
    case Footer = 'footer';
    case Mobile = 'mobile';

    public function getLabel(?string $locale = null): string
    {
        return __('enums.menu_location.'.$this->value, [], $locale);
    }

    public function description(): string
    {
        return __('enums.menu_location_help.'.$this->value);
    }
}
