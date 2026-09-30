<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabelOptions;
use Filament\Support\Contracts\HasLabel;

enum ProjectSector: string implements HasLabel
{
    use HasLabelOptions;

    case Residential = 'residential';
    case Commercial = 'commercial';
    case Hospitality = 'hospitality';
    case Government = 'government';
    case Industrial = 'industrial';

    public function getLabel(?string $locale = null): string
    {
        return __('site.projects.sectors.'.$this->value, [], $locale);
    }
}
