<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabelOptions;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

/** Derived publication state of a blog post (not stored). */
enum PostStatus: string implements HasColor, HasIcon, HasLabel
{
    use HasLabelOptions;

    case Draft = 'draft';
    case Scheduled = 'scheduled';
    case Published = 'published';

    public function getLabel(?string $locale = null): string
    {
        return __('enums.post_status.'.$this->value, [], $locale);
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Scheduled => 'warning',
            self::Published => 'success',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Draft => 'heroicon-o-pencil-square',
            self::Scheduled => 'heroicon-o-clock',
            self::Published => 'heroicon-o-check-circle',
        };
    }
}
