<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\RestrictsDeletionToAdmins;
use App\Filament\Resources\TestimonialResource\Pages;
use App\Models\Project;
use App\Models\Testimonial;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Table;

class TestimonialResource extends Resource
{
    use RestrictsDeletionToAdmins;

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
                    ->label('Project')
                    ->options(fn () => Project::ordered()->pluck('title_en', 'id'))
                    ->searchable(),
                Forms\Components\Tabs::make('Translations')
                    ->columnSpanFull()
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('English')
                            ->schema([
                                Forms\Components\Textarea::make('comment_en')
                                    ->label('Comment (EN)')
                                    ->required()
                                    ->rows(4),
                            ]),
                        Forms\Components\Tabs\Tab::make('Arabic')
                            ->schema([
                                Forms\Components\Textarea::make('comment_ar')
                                    ->label('التعليق (AR)')
                                    ->required()
                                    ->rows(4),
                            ]),
                    ]),
                Forms\Components\TextInput::make('order')
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('is_active')
                    ->default(true),
                SpatieMediaLibraryFileUpload::make('client_photo')
                    ->collection('client_photo')
                    ->image(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('client_photo')->collection('client_photo')->circular()->label('Photo'),
                Tables\Columns\TextColumn::make('client_name')->searchable(),
                Tables\Columns\TextColumn::make('rating')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('project.title_en')->label('Project'),
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
