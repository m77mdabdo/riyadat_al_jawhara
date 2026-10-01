<?php

namespace App\Filament\Resources;

use App\Enums\AdminNavigationGroup;
use App\Filament\Concerns\ManagesPublicRecords;
use App\Filament\Concerns\TranslatesResourceLabels;
use App\Filament\Resources\TestimonialResource\Pages;
use App\Filament\Support\Bilingual;
use App\Filament\Support\ImageUpload;
use App\Filament\Support\Localized;
use App\Models\Project;
use App\Models\Testimonial;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Table;

class TestimonialResource extends Resource
{
    use ManagesPublicRecords, TranslatesResourceLabels;

    protected static ?string $model = Testimonial::class;

    protected static ?string $navigationGroup = AdminNavigationGroup::Portfolio->value;

    protected static ?int $navigationSort = 20;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('Testimonial'))
                    ->description(__('Publish a client\'s words only with their permission. Their name and photo are shown on the home page.'))
                    ->schema([
                        Forms\Components\TextInput::make('client_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('rating')
                            ->options(['1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5'])
                            ->required()
                            ->default('5'),
                        Forms\Components\Select::make('project_id')
                            ->label(__('Project'))
                            ->options(fn () => Project::ordered()->pluck(Localized::column('title'), 'id'))
                            ->searchable(),
                        Bilingual::tabs([
                            'comment' => fn (string $l) => Forms\Components\Textarea::make("comment_{$l}")->label(Bilingual::label('Comment', $l))->required()->rows(4),
                        ]),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true)
                            ->helperText(__('Inactive items are hidden from the website.')),
                    ])->columns(3),
                Forms\Components\Section::make(__('Photo'))
                    ->schema([
                        ImageUpload::make('client_photo', size: '200×200', ratio: '1:1')
                            ->helperText(__('Only with the client\'s consent; leave empty otherwise. Recommended 200×200 (1:1).')),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return static::publicRecordTable($table->columns([
            SpatieMediaLibraryImageColumn::make('client_photo')->collection('client_photo')->circular()->label(__('Photo')),
            Tables\Columns\TextColumn::make('client_name')->searchable(),
            Tables\Columns\TextColumn::make('rating')->numeric()->sortable(),
            Tables\Columns\TextColumn::make('project.'.Localized::column('title'))->label(__('Project')),
            Tables\Columns\IconColumn::make('is_active')->label(__('Active'))->boolean(),
        ]));
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTestimonials::route('/'),
            'create' => Pages\CreateTestimonial::route('/create'),
            'edit' => Pages\EditTestimonial::route('/{record}/edit'),
        ];
    }
}
