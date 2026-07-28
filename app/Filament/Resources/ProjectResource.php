<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\RestrictsDeletionToAdmins;
use App\Filament\Resources\ProjectResource\Pages;
use App\Models\Project;
use App\Models\Service;
use App\Models\StoneType;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Table;

class ProjectResource extends Resource
{
    use RestrictsDeletionToAdmins;

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
                        Forms\Components\Tabs\Tab::make('English')
                            ->schema([
                                Forms\Components\TextInput::make('title_en')
                                    ->label('Title (EN)')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => $set('slug', \Illuminate\Support\Str::slug($state))),
                                Forms\Components\TextInput::make('location_en')
                                    ->label('Location (EN)')
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('description_en')
                                    ->label('Description (EN)')
                                    ->rows(4)
                                    ->columnSpanFull(),
                            ]),
                        Forms\Components\Tabs\Tab::make('Arabic')
                            ->schema([
                                Forms\Components\TextInput::make('title_ar')
                                    ->label('العنوان (AR)')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('location_ar')
                                    ->label('الموقع (AR)')
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('description_ar')
                                    ->label('الوصف (AR)')
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
                Forms\Components\Select::make('service_id')
                    ->label('Service')
                    ->options(fn () => Service::ordered()->pluck('name_en', 'id'))
                    ->searchable(),
                Forms\Components\Select::make('stone_type_id')
                    ->label('Stone Type')
                    ->options(fn () => StoneType::ordered()->pluck('name_en', 'id'))
                    ->searchable(),
                Forms\Components\DatePicker::make('completed_at'),
                Forms\Components\TextInput::make('order')
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('is_featured'),
                Forms\Components\Toggle::make('is_active')
                    ->default(true),
                SpatieMediaLibraryFileUpload::make('cover')
                    ->collection('cover')
                    ->image()
                    ->columnSpanFull(),
                SpatieMediaLibraryFileUpload::make('before')
                    ->collection('before')
                    ->image(),
                SpatieMediaLibraryFileUpload::make('after')
                    ->collection('after')
                    ->image(),
                SpatieMediaLibraryFileUpload::make('gallery')
                    ->collection('gallery')
                    ->image()
                    ->multiple()
                    ->reorderable()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('cover')->collection('cover')->label('Cover'),
                Tables\Columns\TextColumn::make('title_en')->label('Title')->searchable(['title_en', 'title_ar']),
                Tables\Columns\TextColumn::make('service.name_en')->label('Service'),
                Tables\Columns\TextColumn::make('location_en')->label('Location'),
                Tables\Columns\IconColumn::make('is_featured')->boolean(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('order')->numeric()->sortable(),
            ])
            ->defaultSort('order')
            ->filters([
                Tables\Filters\SelectFilter::make('service_id')
                    ->label('Service')
                    ->options(fn () => Service::ordered()->pluck('name_en', 'id')),
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
