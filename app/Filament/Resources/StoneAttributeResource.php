<?php

namespace App\Filament\Resources;

use App\Enums\AdminNavigationGroup;
use App\Enums\StoneAttributeType;
use App\Filament\Concerns\TranslatesResourceLabels;
use App\Filament\Resources\StoneAttributeResource\Pages;
use App\Filament\Support\Localized;
use App\Models\StoneAttribute;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;

class StoneAttributeResource extends Resource
{
    use TranslatesResourceLabels;

    protected static ?string $model = StoneAttribute::class;

    protected static ?string $navigationGroup = AdminNavigationGroup::Catalogue->value;

    protected static ?int $navigationSort = 40;

    protected static ?string $navigationLabel = 'Stone Attributes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('type')
                    ->options(StoneAttributeType::options())
                    ->in(StoneAttributeType::values())
                    ->required()
                    ->live(),
                Forms\Components\TextInput::make('name_en')
                    ->label(__('Name (EN)'))
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => $set('slug', Str::slug((string) $state))),
                Forms\Components\TextInput::make('name_ar')
                    ->label(__('Name (AR)'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->alphaDash()
                    ->unique(ignoreRecord: true, modifyRuleUsing: fn (Unique $rule, Get $get) => $rule->where('type', $get('type')))
                    ->helperText(__('Used in public filter URLs. Unique within its type.')),
                Forms\Components\ColorPicker::make('hex_color')
                    ->label(__('Swatch colour'))
                    ->regex('/^#[0-9A-Fa-f]{6}$/')
                    ->visible(fn (Get $get): bool => $get('type') === StoneAttributeType::Colour->value),
                Forms\Components\TextInput::make('order')
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('is_active')
                    ->default(true),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => StoneAttributeType::labelFor($state)),
                Tables\Columns\ColorColumn::make('hex_color')->label(__('Swatch'))->toggleable(),
                Tables\Columns\TextColumn::make(Localized::column('name'))->label(__('Name'))->searchable(['name_en', 'name_ar']),
                Tables\Columns\TextColumn::make('name_ar')->label('الاسم'),
                Tables\Columns\TextColumn::make('stone_types_count')->counts('stoneTypes')->label(__('Stones')),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('order')->numeric()->sortable(),
            ])
            ->defaultSort('order')
            ->defaultGroup('type')
            ->filters([
                Tables\Filters\SelectFilter::make('type')->options(StoneAttributeType::options()),
                Tables\Filters\TernaryFilter::make('is_active'),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStoneAttributes::route('/'),
            'create' => Pages\CreateStoneAttribute::route('/create'),
            'edit' => Pages\EditStoneAttribute::route('/{record}/edit'),
        ];
    }
}
