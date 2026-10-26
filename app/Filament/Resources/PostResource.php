<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\RestrictsDeletionToAdmins;
use App\Filament\Concerns\TranslatesResourceLabels;
use App\Filament\Resources\PostResource\Pages;
use App\Filament\Support\ImageUpload;
use App\Filament\Support\Localized;
use App\Filament\Support\SeoFields;
use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PostResource extends Resource
{
    use RestrictsDeletionToAdmins, TranslatesResourceLabels;

    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';

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
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => $set('slug', Str::slug($state))),
                                Forms\Components\Textarea::make('excerpt_en')
                                    ->label(__('Excerpt (EN)'))
                                    ->rows(2)
                                    ->columnSpanFull(),
                                Forms\Components\RichEditor::make('body_en')
                                    ->label(__('Body (EN)'))
                                    ->columnSpanFull(),
                            ]),
                        Forms\Components\Tabs\Tab::make(__('Arabic'))
                            ->schema([
                                Forms\Components\TextInput::make('title_ar')
                                    ->label(__('العنوان (AR)'))
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('excerpt_ar')
                                    ->label(__('المقتطف (AR)'))
                                    ->rows(2)
                                    ->columnSpanFull(),
                                Forms\Components\RichEditor::make('body_ar')
                                    ->label(__('المحتوى (AR)'))
                                    ->columnSpanFull(),
                            ]),
                    ]),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Forms\Components\Select::make('blog_category_id')
                    ->label(__('Category'))
                    ->relationship('blogCategory', Localized::column('name'))
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('tags')
                    ->relationship('tags', Localized::column('name'))
                    ->multiple()
                    ->searchable()
                    ->preload(),
                Forms\Components\Toggle::make('is_published')
                    ->live()
                    ->afterStateUpdated(function (bool $state, Forms\Set $set, Forms\Get $get) {
                        if ($state && ! $get('published_at')) {
                            $set('published_at', now());
                        }
                    })
                    ->default(false),
                Forms\Components\DateTimePicker::make('published_at'),
                Forms\Components\Select::make('author_id')
                    ->label(__('Author'))
                    ->relationship('author', 'name')
                    ->searchable()
                    ->default(fn () => auth()->id()),
                ImageUpload::make('cover')
                    ->columnSpanFull(),
                SeoFields::section(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('cover')->collection('cover')->label(__('Cover')),
                Tables\Columns\TextColumn::make(Localized::column('title'))->label(__('Title'))->searchable(['title_en', 'title_ar']),
                Tables\Columns\TextColumn::make('blogCategory.'.Localized::column('name'))->label(__('Category')),
                Tables\Columns\IconColumn::make('is_published')->boolean(),
                Tables\Columns\TextColumn::make('published_at')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('author.name'),
                Tables\Columns\TextColumn::make('views_count')->label(__('Views'))->numeric()->sortable(),
            ])
            ->defaultSort('published_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_published')->label(__('Published')),
                Tables\Filters\SelectFilter::make('blog_category_id')
                    ->label(__('Category'))
                    ->relationship('blogCategory', Localized::column('name')),
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
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
