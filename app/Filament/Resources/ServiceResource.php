<?php

namespace App\Filament\Resources;

use App\Enums\AdminNavigationGroup;
use App\Filament\Concerns\ManagesPublicRecords;
use App\Filament\Concerns\TranslatesResourceLabels;
use App\Filament\Resources\ServiceResource\Pages;
use App\Filament\Support\Bilingual;
use App\Filament\Support\ImageUpload;
use App\Filament\Support\Localized;
use App\Filament\Support\SeoFields;
use App\Models\Service;
use App\Support\ServiceIcon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ServiceResource extends Resource
{
    use ManagesPublicRecords, TranslatesResourceLabels;

    protected static ?string $model = Service::class;

    protected static ?string $navigationGroup = AdminNavigationGroup::Catalogue->value;

    protected static ?int $navigationSort = 10;

    protected static ?string $recordTitleAttribute = 'name_en';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('Content'))
                    ->description(__('Shown on the services page, the home page and in search results.'))
                    ->schema([
                        Bilingual::tabs([
                            'name' => fn (string $l) => Forms\Components\TextInput::make("name_{$l}")
                                ->label(Bilingual::label('Name', $l))
                                ->required()
                                ->maxLength(255),
                            'description' => fn (string $l) => Forms\Components\Textarea::make("description_{$l}")
                                ->label(Bilingual::label('Description', $l))
                                ->rows(4)
                                ->columnSpanFull(),
                        ], slugFrom: 'name'),
                    ]),
                Forms\Components\Section::make(__('Publishing'))
                    ->schema([
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText(__('Part of the page address. Filled from the English name; change it only before publishing.')),
                        Forms\Components\Select::make('icon')
                            ->label(__('Icon'))
                            ->options(Localized::options(ServiceIcon::OPTIONS))
                            ->in(array_keys(ServiceIcon::OPTIONS))
                            ->placeholder(__('Default (Cube)')),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true)
                            ->helperText(__('Inactive items are hidden from the website.')),
                    ])->columns(3),
                Forms\Components\Section::make(__('Image'))
                    ->schema([
                        ImageUpload::make('cover', size: '1600×900', ratio: '16:9')->columnSpanFull(),
                        ImageUpload::altFields('cover'),
                    ]),
                SeoFields::section(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return static::publicRecordTable($table->columns([
            SpatieMediaLibraryImageColumn::make('cover')
                ->collection('cover')
                ->label(__('Cover')),
            Tables\Columns\TextColumn::make(Localized::column('name'))
                ->label(__('Name'))
                ->searchable(['name_en', 'name_ar']),
            Tables\Columns\IconColumn::make('is_active')->label(__('Active'))->boolean(),
        ]));
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
        return $record instanceof Service ? $record->name : '';
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
