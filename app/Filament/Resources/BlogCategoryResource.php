<?php

namespace App\Filament\Resources;

use App\Enums\AdminNavigationGroup;
use App\Filament\Concerns\ManagesPublicRecords;
use App\Filament\Concerns\TranslatesResourceLabels;
use App\Filament\Resources\BlogCategoryResource\Pages;
use App\Filament\Support\Bilingual;
use App\Filament\Support\Localized;
use App\Models\BlogCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BlogCategoryResource extends Resource
{
    use ManagesPublicRecords, TranslatesResourceLabels;

    protected static ?string $model = BlogCategory::class;

    protected static ?string $navigationGroup = AdminNavigationGroup::Blog->value;

    protected static ?int $navigationSort = 20;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('Content'))
                    ->description(__('Groups blog posts; visitors can filter the blog by category.'))
                    ->schema([
                        Bilingual::tabs([
                            'name' => fn (string $l) => Forms\Components\TextInput::make("name_{$l}")->label(Bilingual::label('Name', $l))->required()->maxLength(255),
                        ], slugFrom: 'name'),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText(__('Part of the page address. Filled from the English name; change it only before publishing.')),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return static::publicRecordTable($table->columns([
            Tables\Columns\TextColumn::make(Localized::column('name'))->label(__('Name'))->searchable(['name_en', 'name_ar']),
            Tables\Columns\TextColumn::make('posts_count')->counts('posts')->label(__('Posts')),
        ]));
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBlogCategories::route('/'),
            'create' => Pages\CreateBlogCategory::route('/create'),
            'edit' => Pages\EditBlogCategory::route('/{record}/edit'),
        ];
    }
}
