<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\RestrictsDeletionToAdmins;
use App\Filament\Concerns\TranslatesResourceLabels;
use App\Filament\Resources\TestimonialResource\Pages;
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
    use RestrictsDeletionToAdmins, TranslatesResourceLabels;

    protected static ?string $model = Testimonial::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationGroup = 'Content';

    public static function form(Form $form): Form
    {
        return $form
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
                Forms\Components\Tabs::make('Translations')
                    ->columnSpanFull()
                    ->tabs([
                        Forms\Components\Tabs\Tab::make(__('English'))
                            ->schema([
                                Forms\Components\Textarea::make('comment_en')
                                    ->label(__('Comment (EN)'))
                                    ->required()
                                    ->rows(4),
                            ]),
                        Forms\Components\Tabs\Tab::make(__('Arabic'))
                            ->schema([
                                Forms\Components\Textarea::make('comment_ar')
                                    ->label(__('التعليق (AR)'))
                                    ->required()
                                    ->rows(4),
                            ]),
                    ]),
                Forms\Components\TextInput::make('order')
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('is_active')
                    ->default(true),
                ImageUpload::make('client_photo'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('client_photo')->collection('client_photo')->circular()->label(__('Photo')),
                Tables\Columns\TextColumn::make('client_name')->searchable(),
                Tables\Columns\TextColumn::make('rating')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('project.'.Localized::column('title'))->label(__('Project')),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
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
            'index' => Pages\ListTestimonials::route('/'),
            'create' => Pages\CreateTestimonial::route('/create'),
            'edit' => Pages\EditTestimonial::route('/{record}/edit'),
        ];
    }
}
