<?php

namespace App\Filament\Resources;

use App\Enums\AdminNavigationGroup;
use App\Enums\PostStatus;
use App\Filament\Concerns\ManagesPublicRecords;
use App\Filament\Concerns\TranslatesResourceLabels;
use App\Filament\Resources\PostResource\Pages;
use App\Filament\Support\Bilingual;
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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PostResource extends Resource
{
    use ManagesPublicRecords, TranslatesResourceLabels;

    protected static ?string $model = Post::class;

    protected static ?string $navigationGroup = AdminNavigationGroup::Blog->value;

    protected static ?int $navigationSort = 10;

    protected static ?string $recordTitleAttribute = 'title_en';

    public static function form(Form $form): Form
    {
        $nameField = fn (string $l) => Forms\Components\TextInput::make("name_{$l}")->label(Bilingual::label('Name', $l))->required()->maxLength(255);

        return $form
            ->schema([
                Forms\Components\Section::make(__('Article'))
                    ->schema([
                        Bilingual::tabs([
                            'title' => fn (string $l) => Forms\Components\TextInput::make("title_{$l}")->label(Bilingual::label('Title', $l))->required()->maxLength(255),
                            'excerpt' => fn (string $l) => Forms\Components\Textarea::make("excerpt_{$l}")->label(Bilingual::label('Excerpt', $l))->rows(2)->columnSpanFull()
                                ->helperText(__('One or two sentences shown on the blog list and in search results.')),
                            'body' => fn (string $l) => Forms\Components\RichEditor::make("body_{$l}")->label(Bilingual::label('Body', $l))->columnSpanFull()
                                ->helperText(fn (?Post $record): ?string => $record ? __('About :count min read.', ['count' => $record->readingMinutes($l)]) : null),
                        ], slugFrom: 'title'),
                    ]),
                Forms\Components\Section::make(__('Publishing'))
                    ->description(__('A post is visible once it is published and its date has come; a future date schedules it.'))
                    ->schema([
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText(__('Part of the page address. Filled from the English title; change it only before publishing.')),
                        Forms\Components\Select::make('blog_category_id')
                            ->label(__('Category'))
                            ->relationship('blogCategory', Localized::column('name'))
                            ->searchable()
                            ->preload()
                            ->createOptionForm([Forms\Components\Grid::make(2)->schema([$nameField('ar'), $nameField('en')])]),
                        Forms\Components\Select::make('tags')
                            ->relationship('tags', Localized::column('name'))
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([Forms\Components\Grid::make(2)->schema([$nameField('ar'), $nameField('en')])]),
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
                    ])->columns(3),
                Forms\Components\Section::make(__('Image'))
                    ->schema([
                        ImageUpload::make('cover', size: '1600×900', ratio: '16:9')->columnSpanFull(),
                        ImageUpload::altFields('cover'),
                    ]),
                SeoFields::section(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return static::publicRecordTable($table->columns([
            SpatieMediaLibraryImageColumn::make('cover')->collection('cover')->label(__('Cover')),
            Tables\Columns\TextColumn::make(Localized::column('title'))->label(__('Title'))->searchable(['title_en', 'title_ar'])->wrap(),
            Tables\Columns\TextColumn::make('status')
                ->label(__('Status'))
                ->badge()
                ->state(fn (Post $record): PostStatus => $record->status()),
            Tables\Columns\TextColumn::make('blogCategory.'.Localized::column('name'))->label(__('Category')),
            Tables\Columns\TextColumn::make('published_at')->dateTime()->sortable(),
            Tables\Columns\TextColumn::make('author.name')->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('views_count')->label(__('Views'))->numeric()->sortable(),
        ])->defaultSort('published_at', 'desc'), [
            Tables\Filters\SelectFilter::make('status')
                ->label(__('Status'))
                ->options(PostStatus::options())
                ->query(fn (Builder $query, array $data): Builder => match ($data['value'] ?? null) {
                    PostStatus::Draft->value => $query->where(fn (Builder $q) => $q->where('is_published', false)->orWhereNull('published_at')),
                    PostStatus::Scheduled->value => $query->where('is_published', true)->where('published_at', '>', now()),
                    PostStatus::Published->value => $query->where('is_published', true)->where('published_at', '<=', now()),
                    default => $query,
                }),
            Tables\Filters\SelectFilter::make('blog_category_id')
                ->label(__('Category'))
                ->relationship('blogCategory', Localized::column('name')),
        ]);
    }

    /**
     * @return list<string>
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['title_en', 'title_ar', 'slug'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record instanceof Post ? $record->title : '';
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
