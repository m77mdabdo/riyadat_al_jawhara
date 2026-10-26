<?php

namespace App\Filament\Support;

use Filament\Forms;

/**
 * Collapsible "SEO (optional)" section shared by every content form.
 */
class SeoFields
{
    public static function section(): Forms\Components\Section
    {
        return Forms\Components\Section::make(__('SEO (optional)'))
            ->description(__('Override the title and description shown in Google and when shared. Leave empty to use the page’s own title and text.'))
            ->collapsed()
            ->schema([
                Forms\Components\TextInput::make('meta_title_en')->label(__('Meta title (EN)'))->maxLength(70)->helperText(__('Up to about 60 characters.')),
                Forms\Components\TextInput::make('meta_title_ar')->label(__('عنوان الصفحة (AR)'))->maxLength(70),
                Forms\Components\Textarea::make('meta_description_en')->label(__('Meta description (EN)'))->rows(2)->maxLength(170)->helperText(__('Up to about 155 characters.')),
                Forms\Components\Textarea::make('meta_description_ar')->label(__('وصف الصفحة (AR)'))->rows(2)->maxLength(170),
            ])->columns(2)->columnSpanFull();
    }
}
