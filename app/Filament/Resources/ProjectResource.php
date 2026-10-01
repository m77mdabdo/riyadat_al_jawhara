<?php

namespace App\Filament\Resources;

use App\Enums\AdminNavigationGroup;
use App\Enums\ProjectSector;
use App\Filament\Concerns\ManagesPublicRecords;
use App\Filament\Concerns\TranslatesResourceLabels;
use App\Filament\Resources\ProjectResource\Pages;
use App\Filament\Support\Bilingual;
use App\Filament\Support\ImageUpload;
use App\Filament\Support\Localized;
use App\Filament\Support\SeoFields;
use App\Models\Project;
use App\Models\Service;
use App\Models\StoneType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ProjectResource extends Resource
{
    use ManagesPublicRecords, TranslatesResourceLabels;

    protected static ?string $model = Project::class;

    protected static ?string $navigationGroup = AdminNavigationGroup::Portfolio->value;

    protected static ?int $navigationSort = 10;

    protected static ?string $recordTitleAttribute = 'title_en';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('Project'))
                    ->description(__('Shown on the projects page, the project page and, if featured, on the home page.'))
                    ->schema([
                        Bilingual::tabs([
                            'title' => fn (string $l) => Forms\Components\TextInput::make("title_{$l}")->label(Bilingual::label('Title', $l))->required()->maxLength(255),
                            'location' => fn (string $l) => Forms\Components\TextInput::make("location_{$l}")->label(Bilingual::label('Location', $l))->maxLength(255),
                            'city' => fn (string $l) => Forms\Components\TextInput::make("city_{$l}")->label(Bilingual::label('City', $l))->maxLength(255),
                            'description' => fn (string $l) => Forms\Components\Textarea::make("description_{$l}")->label(Bilingual::label('Description', $l))->rows(4)->columnSpanFull(),
                        ], slugFrom: 'title'),
                    ]),
                Forms\Components\Section::make(__('Details'))
                    ->schema([
                        Forms\Components\Select::make('sector')
                            ->options(ProjectSector::options())
                            ->in(ProjectSector::values()),
                        Forms\Components\TextInput::make('area_m2')
                            ->label(__('Area (m²)'))
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(99999999)
                            ->step(0.01),
                        Forms\Components\DatePicker::make('completed_at'),
                        Forms\Components\Select::make('service_id')
                            ->label(__('Service'))
                            ->options(fn () => Service::ordered()->pluck(Localized::column('name'), 'id'))
                            ->searchable(),
                        Forms\Components\Select::make('stone_type_id')
                            ->label(__('Primary stone type'))
                            ->options(fn () => StoneType::ordered()->pluck(Localized::column('name'), 'id'))
                            ->searchable(),
                        Forms\Components\Select::make('stoneTypes')
                            ->label(__('Additional stones used'))
                            ->relationship('stoneTypes', Localized::column('name'))
                            ->multiple()
                            ->preload()
                            ->searchable(),
                    ])->columns(3),
                Forms\Components\Section::make(__('Client'))
                    ->schema([
                        Forms\Components\TextInput::make('client_name')
                            ->maxLength(255),
                        Forms\Components\Toggle::make('show_client_name')
                            ->label(__('Show client name on the website'))
                            ->helperText(__('Off by default. Turn on only with the client’s permission.'))
                            ->default(false),
                    ])->columns(2),
                Forms\Components\Section::make(__('Publishing'))
                    ->schema([
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText(__('Part of the page address. Filled from the English title; change it only before publishing.')),
                        Forms\Components\Toggle::make('is_featured')
                            ->helperText(__('Featured projects appear on the home page.')),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true)
                            ->helperText(__('Inactive items are hidden from the website.')),
                    ])->columns(3),
                Forms\Components\Section::make(__('Images'))
                    ->schema([
                        ImageUpload::make('cover', size: '1920×1080', ratio: '16:9')->columnSpanFull(),
                        ImageUpload::altFields('cover'),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                ImageUpload::make('before', size: '1920×1080', ratio: '16:9')
                                    ->label(__('Before')),
                                ImageUpload::make('after', size: '1920×1080', ratio: '16:9')
                                    ->label(__('After')),
                            ]),
                        Forms\Components\Placeholder::make('before_after_hint')
                            ->label(__('Before and after'))
                            ->hiddenLabel()
                            ->content(__('Use the same camera position and size for both; the comparison slider only shows when both are uploaded.')),
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
            Tables\Columns\TextColumn::make(Localized::column('title'))->label(__('Title'))->searchable(['title_en', 'title_ar'])->wrap(),
            Tables\Columns\TextColumn::make('service.'.Localized::column('name'))->label(__('Service')),
            Tables\Columns\TextColumn::make(Localized::column('location'))->label(__('Location'))->toggleable(),
            Tables\Columns\TextColumn::make('sector')->badge()->formatStateUsing(fn (?string $state): string => ProjectSector::labelFor($state))->toggleable(),
            Tables\Columns\IconColumn::make('is_featured')->label(__('Featured'))->boolean(),
            Tables\Columns\IconColumn::make('is_active')->label(__('Active'))->boolean(),
        ]), [
            Tables\Filters\SelectFilter::make('service_id')
                ->label(__('Service'))
                ->options(fn () => Service::ordered()->pluck(Localized::column('name'), 'id')),
            Tables\Filters\SelectFilter::make('sector')
                ->options(ProjectSector::options()),
            Tables\Filters\TernaryFilter::make('is_featured'),
        ]);
    }

    /**
     * @return list<string>
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['title_en', 'title_ar', 'slug', 'client_name'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record instanceof Project ? $record->title : '';
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }
}
