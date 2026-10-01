<?php

namespace App\Filament\Resources;

use App\Actions\SiteContent\ImportSiteContent;
use App\Enums\AdminNavigationGroup;
use App\Enums\MenuLinkType;
use App\Enums\MenuLocation;
use App\Filament\Concerns\TranslatesResourceLabels;
use App\Filament\Resources\MenuResource\Pages;
use App\Filament\Support\OptionToggle;
use App\Models\BlogCategory;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Post;
use App\Models\Project;
use App\Models\Service;
use App\Models\StoneCategory;
use App\Models\StoneType;
use App\Support\Navigation;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * The public menus (header, footer, mobile). The three menus are fixed;
 * their items are edited, reordered and nested one level deep.
 */
class MenuResource extends Resource
{
    use TranslatesResourceLabels;

    protected static ?string $model = Menu::class;

    protected static ?string $navigationGroup = AdminNavigationGroup::WebsiteContent->value;

    protected static ?int $navigationSort = 20;

    /** Address forms a custom link may use. */
    public const URL_PATTERN = '~^(/[^\s]*|https?://[^\s]+|#[\w-]*|mailto:[^\s]+|tel:\+?[0-9 ]+)$~';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Placeholder::make('help')
                ->hiddenLabel()
                ->content(fn (?Menu $record) => $record?->location->description())
                ->columnSpanFull(),

            Forms\Components\Section::make(__('Header options'))
                ->description(__('The button and switches at the end of the header.'))
                ->visible(fn (?Menu $record) => $record?->location === MenuLocation::Header)
                ->schema([
                    OptionToggle::make('options.cta_show')->label(__('Show the call-to-action button'))->live(),
                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\TextInput::make('options.cta_label_ar')->label(__('Button text (AR)'))->placeholder(fn () => __('site.common.request_quote', [], 'ar'))->maxLength(40),
                        Forms\Components\TextInput::make('options.cta_label_en')->label(__('Button text (EN)'))->placeholder(fn () => __('site.common.request_quote', [], 'en'))->maxLength(40),
                        Forms\Components\TextInput::make('options.cta_link')->label(__('Button link'))->placeholder('/contact')
                            ->helperText(__('Empty = the contact page. A path such as /stones, or a full address.'))
                            ->regex(self::URL_PATTERN)->extraInputAttributes(['dir' => 'ltr'])->maxLength(500),
                    ])->visible(fn (Get $get) => (bool) $get('options.cta_show')),
                    OptionToggle::make('options.show_language_switch')->label(__('Show the language switch (AR / EN)')),
                    OptionToggle::make('options.show_search')->label(__('Show the search button')),
                ]),

            Forms\Components\Section::make(__('Items'))
                ->description(__('Drag to reorder. When this menu has no active items, the built-in menu is shown.'))
                ->schema([
                    self::itemsRepeater('items', nested: true),
                ]),
        ]);
    }

    private static function itemsRepeater(string $relationship, bool $nested): Forms\Components\Repeater
    {
        $fields = [
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\TextInput::make('label_ar')->label(__('Label (AR)'))->required()->maxLength(60)->extraInputAttributes(['dir' => 'rtl']),
                Forms\Components\TextInput::make('label_en')->label(__('Label (EN)'))->required()->maxLength(60)->extraInputAttributes(['dir' => 'ltr']),
            ]),
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\Select::make('link_type')
                    ->label(__('Links to'))
                    ->options(MenuLinkType::options())
                    ->default(MenuLinkType::Route->value)
                    ->required()
                    ->live(),
                Forms\Components\Select::make('route_name')
                    ->label(__('Page'))
                    ->options(fn () => Navigation::routes())
                    ->required(fn (Get $get) => $get('link_type') === MenuLinkType::Route->value)
                    ->visible(fn (Get $get) => $get('link_type') === MenuLinkType::Route->value),
                Forms\Components\TextInput::make('url')
                    ->label(__('Address'))
                    ->placeholder('/stones?stone_category=marble')
                    ->helperText(__('A path on this site (/…), a full address (https://…), #section, mailto: or tel:.'))
                    ->regex(self::URL_PATTERN)->extraInputAttributes(['dir' => 'ltr'])
                    ->maxLength(500)
                    ->required(fn (Get $get) => $get('link_type') === MenuLinkType::Url->value)
                    ->visible(fn (Get $get) => $get('link_type') === MenuLinkType::Url->value),
            ]),
            Forms\Components\MorphToSelect::make('linkable')
                ->label(__('Record'))
                ->types(self::linkableTypes())
                ->searchable()
                ->required(fn (Get $get) => $get('link_type') === MenuLinkType::Record->value)
                ->visible(fn (Get $get) => $get('link_type') === MenuLinkType::Record->value),
            Forms\Components\Grid::make(3)->schema([
                Forms\Components\Toggle::make('is_active')->label(__('Active'))->default(true),
                Forms\Components\Toggle::make('open_in_new_tab')->label(__('Open in a new tab')),
                Forms\Components\Select::make('icon')->label(__('Icon (optional)'))
                    ->options(collect(MenuItem::ICONS)->mapWithKeys(fn (string $icon) => [$icon => str($icon)->after('heroicon-o-')->headline()->toString()])->all()),
            ]),
        ];

        if ($nested) {
            $fields[] = self::itemsRepeater('children', nested: false)
                ->label(__('Sub-items'))
                ->helperText(__('Header: a small drop-down list. Footer: the item becomes a column title.'))
                ->defaultItems(0)
                ->collapsed();
        }

        return Forms\Components\Repeater::make($relationship)
            ->hiddenLabel($nested)
            ->relationship($relationship)
            ->orderColumn('order')
            ->reorderableWithDragAndDrop()
            ->collapsible()
            ->cloneable()
            ->itemLabel(fn (array $state): ?string => $state['label_'.(app()->getLocale() === 'ar' ? 'ar' : 'en')] ?? null)
            ->addActionLabel(__('Add item'))
            ->mutateRelationshipDataBeforeCreateUsing(function (array $data, $livewire): array {
                $data['menu_id'] = $livewire->getRecord()->getKey();

                return $data;
            })
            ->schema($fields);
    }

    /**
     * @return list<Forms\Components\MorphToSelect\Type>
     */
    private static function linkableTypes(): array
    {
        $type = fn (string $model, string $label, string $title) => Forms\Components\MorphToSelect\Type::make($model)
            ->label(__($label))
            ->titleAttribute($title)
            ->getOptionLabelFromRecordUsing(fn (Model $record) => (string) $record->getAttribute($title));

        return [
            $type(Service::class, 'Service', app()->getLocale() === 'ar' ? 'name_ar' : 'name_en'),
            $type(StoneType::class, 'Stone Type', app()->getLocale() === 'ar' ? 'name_ar' : 'name_en'),
            $type(StoneCategory::class, 'Stone Category', app()->getLocale() === 'ar' ? 'name_ar' : 'name_en'),
            $type(Project::class, 'Project', app()->getLocale() === 'ar' ? 'title_ar' : 'title_en'),
            $type(Post::class, 'Post', app()->getLocale() === 'ar' ? 'title_ar' : 'title_en'),
            $type(BlogCategory::class, 'Blog Category', app()->getLocale() === 'ar' ? 'name_ar' : 'name_en'),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('location')->label(__('Menu'))->badge(),
                Tables\Columns\TextColumn::make('location_help')->label(__('Where'))
                    ->state(fn (Menu $record) => $record->location->description())->wrap(),
                Tables\Columns\TextColumn::make('all_items_count')->label(__('Items'))->counts('allItems'),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->paginated(false);
    }

    public static function missingAction(): Action
    {
        return Action::make('addMissing')
            ->label(__('Add missing menus'))
            ->icon('heroicon-o-plus')
            ->visible(fn () => Menu::query()->count() < count(MenuLocation::cases()) && self::canEditAny())
            ->action(function () {
                app(ImportSiteContent::class)->menus();
                Notification::make()->title(__('Menus added with the current links'))->success()->send();
            });
    }

    private static function canEditAny(): bool
    {
        return auth()->user()?->can('update', Menu::class) ?? false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMenus::route('/'),
            'edit' => Pages\EditMenu::route('/{record}/edit'),
        ];
    }
}
