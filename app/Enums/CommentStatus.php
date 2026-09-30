<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabelOptions;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum CommentStatus: string implements HasColor, HasLabel
{
    use HasLabelOptions;

    case Pending = 'pending';
    case Approved = 'approved';
    case Spam = 'spam';

    public function getLabel(?string $locale = null): string
    {
        return __('enums.comment_status.'.$this->value, [], $locale);
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Approved => 'success',
            self::Spam => 'danger',
        };
    }

    public static function colorFor(?string $value): string
    {
        return self::tryFrom((string) $value)?->getColor() ?? 'gray';
    }
}
