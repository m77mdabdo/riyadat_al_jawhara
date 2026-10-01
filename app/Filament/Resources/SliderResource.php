<?php

namespace App\Filament\Resources;

use App\Enums\AdminNavigationGroup;
use App\Filament\Concerns\ManagesPublicRecords;
use App\Filament\Concerns\TranslatesResourceLabels;
use App\Filament\Resources\SliderResource\Pages;
use App\Filament\Support\Bilingual;
use App\Filament\Support\ImageUpload;
use App\Filament\Support\Localized;
use App\Models\Slider;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Table;

class SliderResource extends Resource
{
    use ManagesPublicRecords, TranslatesResourceLabels;

    protected static ?string $model = Slider::class;

    protected static ?string $navigationGroup = AdminNavigationGroup::WebsiteContent->value;

    protected static ?int $navigationSort = 30;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('Slide'))
                    ->description(__('Hero slides at the top of the home page. Without slides, the home page shows the default hero.'))
                    ->schema([
                        Bilingual::tabs([
                            'title' => fn (string $l) => Forms\Components\TextInput::make("title_{$l}")->label(Bilingual::label('Title', $l))->required()->maxLength(255),
                            'subtitle' => fn (string $l) => Forms\Components\TextInput::make("subtitle_{$l}")->label(Bilingual::label('Subtitle', $l))->maxLength(255),
                            'button_text' => fn (string $l) => Forms\Components\TextInput::make("button_text_{$l}")->label(Bilingual::label('Button Text', $l))->maxLength(255),
                        ]),
                        Forms\Components\TextInput::make('button_link')
                            ->helperText(__('A page of this site like /contact opens in the visitor\'s language automatically. Full https:// links are used as they are.'))
                            ->maxLength(255)
                            ->regex('~^(/[^\s]*|https?://[^\s]+|#[\w-]*)$~'),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true)
                            ->helperText(__('Inactive items are hidden from the website.')),
                    ]),
                Forms\Components\Section::make(__('Image'))
                    ->schema([
                        ImageUpload::make('image', size: '1920×1080', ratio: '16:9')->columnSpanFull(),
                        ImageUpload::altFields('image'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return static::publicRecordTable($table->columns([
            SpatieMediaLibraryImageColumn::make('image')->collection('image')->label(__('Image')),
            Tables\Columns\TextColumn::make(Localized::column('title'))->label(__('Title'))->searchable(['title_en', 'title_ar']),
            Tables\Columns\IconColumn::make('is_active')->label(__('Active'))->boolean(),
        ]));
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSliders::route('/'),
            'create' => Pages\CreateSlider::route('/create'),
            'edit' => Pages\EditSlider::route('/{record}/edit'),
        ];
    }
}
