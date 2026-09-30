<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\RestrictsDeletionToAdmins;
use App\Filament\Resources\StoneCategoryResource\Pages;
use App\Filament\Support\ImageUpload;
use App\Models\StoneCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class StoneCategoryResource extends Resource
{
    use RestrictsDeletionToAdmins;

    protected static ?string $model = StoneCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationGroup = 'Stones';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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
                                    ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => $set('slug', Str::slug($state))),
                                Forms\Components\Textarea::make('description_en')
                                    ->label('Description (EN)')
                                    ->rows(3)
                                    ->maxLength(2000),
                            ]),
                        Forms\Components\Tabs\Tab::make('Arabic')
                            ->schema([
                                Forms\Components\TextInput::make('name_ar')
                                    ->label('الاسم (AR)')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('description_ar')
                                    ->label('الوصف (AR)')
                                    ->rows(3)
                                    ->maxLength(2000),
                            ]),
                    ]),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('order')
                    ->numeric()
                    ->default(0),
                ImageUpload::make('cover')
                    ->label('Category image')
                    ->helperText('Shown on the home tiles and the category landing section. JPG, PNG or WebP, up to 5 MB. Without it, a stone texture is used.')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name_en')->label('Name')->searchable(['name_en', 'name_ar']),
                Tables\Columns\TextColumn::make('stoneTypes_count')->counts('stoneTypes')->label('Stone Types'),
                Tables\Columns\TextColumn::make('order')->numeric()->sortable(),
            ])
            ->defaultSort('order')
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
            'index' => Pages\ListStoneCategories::route('/'),
            'create' => Pages\CreateStoneCategory::route('/create'),
            'edit' => Pages\EditStoneCategory::route('/{record}/edit'),
        ];
    }
}
