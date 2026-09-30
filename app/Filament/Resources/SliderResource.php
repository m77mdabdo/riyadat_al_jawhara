<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\RestrictsDeletionToAdmins;
use App\Filament\Concerns\TranslatesResourceLabels;
use App\Filament\Resources\SliderResource\Pages;
use App\Filament\Support\ImageUpload;
use App\Filament\Support\Localized;
use App\Models\Slider;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Table;

class SliderResource extends Resource
{
    use RestrictsDeletionToAdmins, TranslatesResourceLabels;

    protected static ?string $model = Slider::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'Content';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Translations')
                    ->columnSpanFull()
                    ->tabs([
                        Forms\Components\Tabs\Tab::make(__('English'))
                            ->schema([
                                Forms\Components\TextInput::make('title_en')
                                    ->label(__('Title (EN)'))
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('subtitle_en')
                                    ->label(__('Subtitle (EN)'))
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('button_text_en')
                                    ->label(__('Button Text (EN)'))
                                    ->maxLength(255),
                            ]),
                        Forms\Components\Tabs\Tab::make(__('Arabic'))
                            ->schema([
                                Forms\Components\TextInput::make('title_ar')
                                    ->label(__('العنوان (AR)'))
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('subtitle_ar')
                                    ->label(__('العنوان الفرعي (AR)'))
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('button_text_ar')
                                    ->label(__('نص الزر (AR)'))
                                    ->maxLength(255),
                            ]),
                    ]),
                Forms\Components\TextInput::make('button_link')
                    ->helperText(__('A page of this site like /contact opens in the visitor\'s language automatically. Full https:// links are used as they are.'))
                    ->maxLength(255),
                Forms\Components\TextInput::make('order')
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('is_active')
                    ->default(true),
                ImageUpload::make('image')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('image')->collection('image')->label(__('Image')),
                Tables\Columns\TextColumn::make(Localized::column('title'))->label(__('Title'))->searchable(['title_en', 'title_ar']),
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
            'index' => Pages\ListSliders::route('/'),
            'create' => Pages\CreateSlider::route('/create'),
            'edit' => Pages\EditSlider::route('/{record}/edit'),
        ];
    }
}
