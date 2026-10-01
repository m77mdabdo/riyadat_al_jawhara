<?php

namespace App\Filament\Resources;

use App\Enums\AdminNavigationGroup;
use App\Filament\Concerns\ManagesPublicRecords;
use App\Filament\Concerns\TranslatesResourceLabels;
use App\Filament\Resources\StoneCategoryResource\Pages;
use App\Filament\Support\Bilingual;
use App\Filament\Support\ImageUpload;
use App\Filament\Support\Localized;
use App\Models\StoneCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StoneCategoryResource extends Resource
{
    use ManagesPublicRecords, TranslatesResourceLabels;

    protected static ?string $model = StoneCategory::class;

    protected static ?string $navigationGroup = AdminNavigationGroup::Catalogue->value;

    protected static ?int $navigationSort = 30;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('Content'))
                    ->description(__('Categories group the stones in the catalogue and on the home page tiles.'))
                    ->schema([
                        Bilingual::tabs([
                            'name' => fn (string $l) => Forms\Components\TextInput::make("name_{$l}")->label(Bilingual::label('Name', $l))->required()->maxLength(255),
                            'description' => fn (string $l) => Forms\Components\Textarea::make("description_{$l}")->label(Bilingual::label('Description', $l))->rows(3)->maxLength(2000),
                        ], slugFrom: 'name'),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText(__('Part of the page address. Filled from the English name; change it only before publishing.')),
                    ]),
                Forms\Components\Section::make(__('Image'))
                    ->schema([
                        ImageUpload::make('cover', size: '1280×853', ratio: '3:2')
                            ->label(__('Category image'))
                            ->helperText(__('Shown on the home tiles and the category landing section. Recommended 1280×853 (3:2). JPG, PNG or WebP, up to :size MB. Without it, a stone texture is used.', ['size' => round(ImageUpload::maxKb() / 1024)]))
                            ->columnSpanFull(),
                        ImageUpload::altFields('cover'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return static::publicRecordTable($table->columns([
            Tables\Columns\TextColumn::make(Localized::column('name'))->label(__('Name'))->searchable(['name_en', 'name_ar']),
            Tables\Columns\TextColumn::make('stoneTypes_count')->counts('stoneTypes')->label(__('Stone Types')),
        ]));
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStoneCategories::route('/'),
            'create' => Pages\CreateStoneCategory::route('/create'),
            'edit' => Pages\EditStoneCategory::route('/{record}/edit'),
        ];
    }
}
