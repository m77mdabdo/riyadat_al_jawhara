<?php

namespace App\Filament\Resources;

use App\Enums\AdminNavigationGroup;
use App\Filament\Concerns\ManagesPublicRecords;
use App\Filament\Concerns\TranslatesResourceLabels;
use App\Filament\Resources\TeamMemberResource\Pages;
use App\Filament\Support\Bilingual;
use App\Filament\Support\ImageUpload;
use App\Filament\Support\Localized;
use App\Models\TeamMember;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Table;

class TeamMemberResource extends Resource
{
    use ManagesPublicRecords, TranslatesResourceLabels;

    protected static ?string $model = TeamMember::class;

    protected static ?string $navigationGroup = AdminNavigationGroup::WebsiteContent->value;

    protected static ?int $navigationSort = 70;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('Team member'))
                    ->description(__('Shown on the About page. Publish a person\'s name, photo and contact details only with their consent.'))
                    ->schema([
                        Bilingual::tabs([
                            'name' => fn (string $l) => Forms\Components\TextInput::make("name_{$l}")->label(Bilingual::label('Name', $l))->required()->maxLength(255),
                            'position' => fn (string $l) => Forms\Components\TextInput::make("position_{$l}")->label(Bilingual::label('Position', $l))->maxLength(255),
                        ]),
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true)
                            ->helperText(__('Inactive items are hidden from the website.')),
                    ])->columns(3),
                Forms\Components\Section::make(__('Photo'))
                    ->schema([
                        ImageUpload::make('photo', size: '600×600', ratio: '1:1'),
                        ImageUpload::altFields('photo'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return static::publicRecordTable($table->columns([
            SpatieMediaLibraryImageColumn::make('photo')->collection('photo')->circular()->label(__('Photo')),
            Tables\Columns\TextColumn::make(Localized::column('name'))->label(__('Name'))->searchable(['name_en', 'name_ar']),
            Tables\Columns\TextColumn::make(Localized::column('position'))->label(__('Position')),
            Tables\Columns\TextColumn::make('phone'),
            Tables\Columns\TextColumn::make('email'),
            Tables\Columns\IconColumn::make('is_active')->label(__('Active'))->boolean(),
        ]), actions: [static::previewAction('team-member')]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTeamMembers::route('/'),
            'create' => Pages\CreateTeamMember::route('/create'),
            'edit' => Pages\EditTeamMember::route('/{record}/edit'),
        ];
    }
}
