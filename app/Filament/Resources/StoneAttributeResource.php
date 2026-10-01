<?php

namespace App\Filament\Resources;

use App\Enums\AdminNavigationGroup;
use App\Enums\StoneAttributeType;
use App\Filament\Concerns\ManagesPublicRecords;
use App\Filament\Concerns\TranslatesResourceLabels;
use App\Filament\Resources\StoneAttributeResource\Pages;
use App\Filament\Support\Bilingual;
use App\Filament\Support\Localized;
use App\Models\StoneAttribute;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Unique;

class StoneAttributeResource extends Resource
{
    use ManagesPublicRecords, TranslatesResourceLabels;

    protected static ?string $model = StoneAttribute::class;

    protected static ?string $navigationGroup = AdminNavigationGroup::Catalogue->value;

    protected static ?int $navigationSort = 40;

    protected static ?string $navigationLabel = 'Stone Attributes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('Value'))
                    ->description(__('Finishes, colours, sizes and applications offered as filters and shown on stone pages.'))
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->options(StoneAttributeType::options())
                            ->in(StoneAttributeType::values())
                            ->required()
                            ->live(),
                        Bilingual::tabs([
                            'name' => fn (string $l) => Forms\Components\TextInput::make("name_{$l}")->label(Bilingual::label('Name', $l))->required()->maxLength(255),
                        ], slugFrom: 'name'),
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
                        Forms\Components\Toggle::make('is_active')
                            ->default(true)
                            ->helperText(__('Inactive values are hidden from filters and stone pages.')),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return static::publicRecordTable($table->columns([
            Tables\Columns\TextColumn::make('type')
                ->badge()
                ->formatStateUsing(fn (string $state): string => StoneAttributeType::labelFor($state)),
            Tables\Columns\ColorColumn::make('hex_color')->label(__('Swatch'))->toggleable(),
            Tables\Columns\TextColumn::make(Localized::column('name'))->label(__('Name'))->searchable(['name_en', 'name_ar']),
            Tables\Columns\TextColumn::make('stone_types_count')->counts('stoneTypes')->label(__('Stones')),
            Tables\Columns\IconColumn::make('is_active')->label(__('Active'))->boolean(),
        ])->defaultGroup('type'), [
            Tables\Filters\SelectFilter::make('type')->options(StoneAttributeType::options()),
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
