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
        return Forms\Components\Section::make('SEO (optional)')
            ->description('Override the title and description shown in Google and when shared. Leave empty to use the page’s own title and text.')
            ->collapsed()
            ->schema([
                Forms\Components\TextInput::make('meta_title_en')->label('Meta title (EN)')->maxLength(70)->helperText('Up to about 60 characters.'),
                Forms\Components\TextInput::make('meta_title_ar')->label('عنوان الصفحة (AR)')->maxLength(70),
                Forms\Components\Textarea::make('meta_description_en')->label('Meta description (EN)')->rows(2)->maxLength(170)->helperText('Up to about 155 characters.'),
                Forms\Components\Textarea::make('meta_description_ar')->label('وصف الصفحة (AR)')->rows(2)->maxLength(170),
            ])->columns(2)->columnSpanFull();
    }
}
