<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\RestrictsDeletionToAdmins;
use App\Filament\Resources\StoneTypeResource\Pages;
use App\Models\StoneCategory;
use App\Models\StoneType;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Table;

class StoneTypeResource extends Resource
{
    use RestrictsDeletionToAdmins;

    protected static ?string $model = StoneType::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = 'Stones';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('stone_category_id')
                    ->label('Category')
                    ->options(fn () => StoneCategory::ordered()->pluck('name_en', 'id'))
                    ->required()
                    ->searchable(),
                Forms\Components\Tabs::make('Translations')
                    ->columnSpanFull()
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('English')
                            ->schema([
                                Forms\Components\TextInput::make('name_en')
                                    ->label('Name (EN)')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => $set('slug', \Illuminate\Support\Str::slug($state))),
                                Forms\Components\TextInput::make('origin_en')
                                    ->label('Origin (EN)')
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('description_en')
                                    ->label('Description (EN)')
                                    ->rows(4)
                                    ->columnSpanFull(),
                            ]),
                        Forms\Components\Tabs\Tab::make('Arabic')
                            ->schema([
                                Forms\Components\TextInput::make('name_ar')
                                    ->label('الاسم (AR)')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('origin_ar')
                                    ->label('المنشأ (AR)')
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
                Forms\Components\TextInput::make('order')
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('is_active')
                    ->default(true),
                SpatieMediaLibraryFileUpload::make('cover')
                    ->collection('cover')
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
                Tables\Columns\TextColumn::make('name_en')->label('Name')->searchable(['name_en', 'name_ar']),
                Tables\Columns\TextColumn::make('stoneCategory.name_en')->label('Category'),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('order')->numeric()->sortable(),
            ])
            ->defaultSort('order')
            ->filters([
                Tables\Filters\SelectFilter::make('stone_category_id')
                    ->label('Category')
                    ->options(fn () => StoneCategory::ordered()->pluck('name_en', 'id')),
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
            'index' => Pages\ListStoneTypes::route('/'),
            'create' => Pages\CreateStoneType::route('/create'),
            'edit' => Pages\EditStoneType::route('/{record}/edit'),
        ];
    }
}
