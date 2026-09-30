<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\RestrictsDeletionToAdmins;
use App\Filament\Concerns\TranslatesResourceLabels;
use App\Filament\Resources\ProjectResource\Pages;
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
use Illuminate\Support\Str;

class ProjectResource extends Resource
{
    use RestrictsDeletionToAdmins, TranslatesResourceLabels;

    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'Content';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Translations')
                    ->columnSpanFull()
                    ->tabs([
                        Forms\Components\Tabs\Tab::make(__('English'))
                            ->schema([
                                Forms\Components\TextInput::make('title_en')
                                    ->label(__('Title (EN)'))
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => $set('slug', Str::slug($state))),
                                Forms\Components\TextInput::make('location_en')
                                    ->label(__('Location (EN)'))
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('city_en')
                                    ->label(__('City (EN)'))
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('description_en')
                                    ->label(__('Description (EN)'))
                                    ->rows(4)
                                    ->columnSpanFull(),
                            ]),
                        Forms\Components\Tabs\Tab::make(__('Arabic'))
                            ->schema([
                                Forms\Components\TextInput::make('title_ar')
                                    ->label(__('العنوان (AR)'))
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('location_ar')
                                    ->label(__('الموقع (AR)'))
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('city_ar')
                                    ->label(__('المدينة (AR)'))
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('description_ar')
                                    ->label(__('الوصف (AR)'))
                                    ->rows(4)
                                    ->columnSpanFull(),
                            ]),
                    ]),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('client_name')
                    ->maxLength(255),
                Forms\Components\Toggle::make('show_client_name')
                    ->label(__('Show client name on the website'))
                    ->helperText(__('Off by default. Turn on only with the client’s permission.'))
                    ->default(false),
                Forms\Components\Select::make('sector')
                    ->options(Localized::options(array_combine(Project::SECTORS, array_map('ucfirst', Project::SECTORS))))
                    ->in(Project::SECTORS),
                Forms\Components\TextInput::make('area_m2')
                    ->label(__('Area (m²)'))
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(99999999)
                    ->step(0.01),
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
                Forms\Components\DatePicker::make('completed_at'),
                Forms\Components\TextInput::make('order')
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('is_featured'),
                Forms\Components\Toggle::make('is_active')
                    ->default(true),
                ImageUpload::make('cover')
                    ->columnSpanFull(),
                ImageUpload::make('before'),
                ImageUpload::make('after'),
                ImageUpload::make('gallery')
                    ->multiple()
                    ->reorderable()
                    ->columnSpanFull(),
                SeoFields::section(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('cover')->collection('cover')->label(__('Cover')),
                Tables\Columns\TextColumn::make(Localized::column('title'))->label(__('Title'))->searchable(['title_en', 'title_ar']),
                Tables\Columns\TextColumn::make('service.'.Localized::column('name'))->label(__('Service')),
                Tables\Columns\TextColumn::make('location_en')->label(__('Location')),
                Tables\Columns\TextColumn::make('sector')->badge()->formatStateUsing(fn (?string $state): string => __(ucfirst((string) $state)))->toggleable(),
                Tables\Columns\IconColumn::make('is_featured')->boolean(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('order')->numeric()->sortable(),
            ])
            ->defaultSort('order')
            ->filters([
                Tables\Filters\SelectFilter::make('service_id')
                    ->label(__('Service'))
                    ->options(fn () => Service::ordered()->pluck(Localized::column('name'), 'id')),
                Tables\Filters\SelectFilter::make('sector')
                    ->options(Localized::options(array_combine(Project::SECTORS, array_map('ucfirst', Project::SECTORS)))),
                Tables\Filters\TernaryFilter::make('is_featured'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
