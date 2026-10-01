<?php

namespace App\Filament\Resources;

use App\Enums\AdminNavigationGroup;
use App\Enums\StoneOriginType;
use App\Filament\Concerns\ManagesPublicRecords;
use App\Filament\Concerns\TranslatesResourceLabels;
use App\Filament\Resources\StoneTypeResource\Pages;
use App\Filament\Support\Bilingual;
use App\Filament\Support\ImageUpload;
use App\Filament\Support\Localized;
use App\Filament\Support\SeoFields;
use App\Models\StoneCategory;
use App\Models\StoneType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class StoneTypeResource extends Resource
{
    use ManagesPublicRecords, TranslatesResourceLabels;

    protected static ?string $model = StoneType::class;

    protected static ?string $navigationGroup = AdminNavigationGroup::Catalogue->value;

    protected static ?int $navigationSort = 20;

    protected static ?string $recordTitleAttribute = 'name_en';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('Content'))
                    ->description(__('Shown on the stone page, in the catalogue PDF and in search results.'))
                    ->schema([
                        Forms\Components\Select::make('stone_category_id')
                            ->label(__('Category'))
                            ->options(fn () => StoneCategory::ordered()->pluck(Localized::column('name'), 'id'))
                            ->required()
                            ->searchable(),
                        Bilingual::tabs([
                            'name' => fn (string $l) => Forms\Components\TextInput::make("name_{$l}")->label(Bilingual::label('Name', $l))->required()->maxLength(255),
                            'origin' => fn (string $l) => Forms\Components\TextInput::make("origin_{$l}")->label(Bilingual::label('Origin', $l))->maxLength(255),
                            'description' => fn (string $l) => Forms\Components\Textarea::make("description_{$l}")->label(Bilingual::label('Description', $l))->rows(4)->columnSpanFull(),
                        ], slugFrom: 'name'),
                    ]),
                Forms\Components\Section::make(__('Specifications'))
                    ->description(__('Values are managed under Stones → Stone Attributes.'))
                    ->schema([
                        Forms\Components\Select::make('origin_type')
                            ->label(__('Local / Imported'))
                            ->options(StoneOriginType::options())
                            ->in(StoneOriginType::values())
                            ->placeholder(__('Not specified')),
                        static::attributeSelect('finishes', 'Finishes'),
                        static::attributeSelect('colours', 'Colours'),
                        static::attributeSelect('sizes', 'Sizes / Thickness'),
                        static::attributeSelect('applications', 'Applications'),
                    ])->columns(2),
                Forms\Components\Section::make(__('Publishing'))
                    ->schema([
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText(__('Part of the page address. Filled from the English name; change it only before publishing.')),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true)
                            ->helperText(__('Inactive items are hidden from the website.')),
                        Forms\Components\Toggle::make('spec_sheet_enabled')
                            ->label(__('Offer a PDF spec sheet'))
                            ->default(true)
                            ->helperText(__('Shows the "Download spec sheet" button on the stone page.')),
                    ])->columns(3),
                Forms\Components\Section::make(__('Images'))
                    ->schema([
                        ImageUpload::make('cover', size: '1200×1200', ratio: '1:1'),
                        ImageUpload::altFields('cover'),
                        ImageUpload::make('gallery', size: '1600×1200', ratio: '4:3')
                            ->multiple()
                            ->reorderable()
                            ->panelLayout('grid')
                            ->helperText(__('Drag to reorder. Recommended 1600×1200 (4:3). JPG, PNG or WebP, up to :size MB.', ['size' => round(ImageUpload::maxKb() / 1024)]))
                            ->columnSpanFull(),
                    ]),
                SeoFields::section(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return static::publicRecordTable($table->columns([
            SpatieMediaLibraryImageColumn::make('cover')->collection('cover')->label(__('Cover')),
            Tables\Columns\TextColumn::make(Localized::column('name'))->label(__('Name'))->searchable(['name_en', 'name_ar']),
            Tables\Columns\TextColumn::make('stoneCategory.'.Localized::column('name'))->label(__('Category')),
            Tables\Columns\IconColumn::make('is_active')->label(__('Active'))->boolean(),
        ]), [
            Tables\Filters\SelectFilter::make('stone_category_id')
                ->label(__('Category'))
                ->options(fn () => StoneCategory::ordered()->pluck(Localized::column('name'), 'id')),
        ]);
    }

    /**
     * @return list<string>
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['name_en', 'name_ar', 'slug'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record instanceof StoneType ? $record->name : '';
    }

    protected static function attributeSelect(string $relationship, string $label): Forms\Components\Select
    {
        return Forms\Components\Select::make($relationship)
            ->label(__($label))
            ->relationship($relationship, Localized::column('name'), fn (Builder $query) => $query->orderBy('stone_attributes.order'))
            ->multiple()
            ->preload()
            ->searchable();
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStoneTypes::route('/'),
            'create' => Pages\CreateStoneType::route('/create'),
            'edit' => Pages\EditStoneType::route('/{record}/edit'),
        ];
    }
}
