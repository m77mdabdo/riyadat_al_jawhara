<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabelOptions;
use Filament\Support\Contracts\HasLabel;

/** What a menu item points to. */
enum MenuLinkType: string implements HasLabel
{
    use HasLabelOptions;

    /** A page of the site (named route). */
    case Route = 'route';
    /** A service, stone, project, article or category. */
    case Record = 'record';
    /** Any address: /path, https://…, #anchor, mailto:, tel:. */
    case Url = 'url';

    public function getLabel(?string $locale = null): string
    {
        return __('enums.menu_link_type.'.$this->value, [], $locale);
    }
}
