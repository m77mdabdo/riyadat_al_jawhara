<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\RestrictsDeletionToAdmins;
use App\Filament\Concerns\TranslatesResourceLabels;
use App\Filament\Resources\StoneTypeResource\Pages;
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
use Illuminate\Support\Str;

class StoneTypeResource extends Resource
{
    use RestrictsDeletionToAdmins, TranslatesResourceLabels;

    protected static ?string $model = StoneType::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = 'Stones';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('stone_category_id')
                    ->label(__('Category'))
                    ->options(fn () => StoneCategory::ordered()->pluck(Localized::column('name'), 'id'))
                    ->required()
                    ->searchable(),
                Forms\Components\Tabs::make('Translations')
                    ->columnSpanFull()
                    ->tabs([
                        Forms\Components\Tabs\Tab::make(__('English'))
                            ->schema([
                                Forms\Components\TextInput::make('name_en')
                                    ->label(__('Name (EN)'))
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => $set('slug', Str::slug($state))),
                                Forms\Components\TextInput::make('origin_en')
                                    ->label(__('Origin (EN)'))
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('description_en')
                                    ->label(__('Description (EN)'))
                                    ->rows(4)
                                    ->columnSpanFull(),
                            ]),
                        Forms\Components\Tabs\Tab::make(__('Arabic'))
                            ->schema([
                                Forms\Components\TextInput::make('name_ar')
                                    ->label(__('الاسم (AR)'))
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('origin_ar')
                                    ->label(__('المنشأ (AR)'))
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
                Forms\Components\Section::make(__('Specifications'))
                    ->description(__('Values are managed under Stones → Stone Attributes.'))
                    ->schema([
                        Forms\Components\Select::make('origin_type')
                            ->label(__('Local / Imported'))
                            ->options([
                                StoneType::ORIGIN_LOCAL => __('Local'),
                                StoneType::ORIGIN_IMPORTED => __('Imported'),
                            ])
                            ->in(StoneType::ORIGIN_TYPES)
                            ->placeholder(__('Not specified')),
                        static::attributeSelect('finishes', 'Finishes'),
                        static::attributeSelect('colours', 'Colours'),
                        static::attributeSelect('sizes', 'Sizes / Thickness'),
                        static::attributeSelect('applications', 'Applications'),
                    ])->columns(2),
                Forms\Components\TextInput::make('order')
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('is_active')
                    ->default(true),
                ImageUpload::make('cover'),
                ImageUpload::make('gallery')
                    ->multiple()
                    ->reorderable()
                    ->columnSpanFull(),
                SeoFields::section(),
            ]);
    }

    /**
     * Multi-select bound to one of the per-type relations on StoneType, so each
     * select only lists (and only syncs) values of its own type.
     */
    private static function attributeSelect(string $relationship, string $label): Forms\Components\Select
    {
        return Forms\Components\Select::make($relationship)
            ->label(__($label))
            ->relationship($relationship, Localized::column('name'), fn (Builder $query) => $query->orderBy('stone_attributes.order'))
            ->multiple()
            ->preload()
            ->searchable();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('cover')->collection('cover')->label(__('Cover')),
                Tables\Columns\TextColumn::make(Localized::column('name'))->label(__('Name'))->searchable(['name_en', 'name_ar']),
                Tables\Columns\TextColumn::make('stoneCategory.'.Localized::column('name'))->label(__('Category')),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('order')->numeric()->sortable(),
            ])
            ->defaultSort('order')
            ->filters([
                Tables\Filters\SelectFilter::make('stone_category_id')
                    ->label(__('Category'))
                    ->options(fn () => StoneCategory::ordered()->pluck(Localized::column('name'), 'id')),
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
