<?php

namespace App\Filament\Resources;

use App\Actions\SiteContent\ImportSiteContent;
use App\Enums\AdminNavigationGroup;
use App\Enums\PageKey;
use App\Filament\Concerns\TranslatesResourceLabels;
use App\Filament\Resources\PageResource\Pages;
use App\Filament\Support\ImageUpload;
use App\Filament\Support\OptionToggle;
use App\Filament\Support\SeoFields;
use App\Filament\Support\SiteTextFields;
use App\Models\Page;
use App\Models\Setting;
use App\Support\ContactForm;
use App\Support\PageBlocks;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * The fixed public pages: header texts and banner, extra content blocks,
 * page-specific settings (About texts, contact form, legal text) and SEO.
 */
class PageResource extends Resource
{
    use TranslatesResourceLabels;

    protected static ?string $model = Page::class;

    protected static ?string $navigationGroup = AdminNavigationGroup::WebsiteContent->value;

    protected static ?int $navigationSort = 40;

    /** Settings columns edited on the About / legal pages (admins only, as in Settings). */
    public const SETTING_FIELDS = [
        'about' => ['about_ar', 'about_en', 'vision_ar', 'vision_en', 'mission_ar', 'mission_en'],
        'privacy' => ['privacy_policy_ar', 'privacy_policy_en'],
        'terms' => ['terms_ar', 'terms_en'],
    ];

    /** Contact-page texts edited on the Contact page. */
    public const CONTACT_TEXTS = [
        'site.contact.get_in_touch' => 'Heading above the form',
        'site.contact.success_message' => 'Message after sending',
        'site.contact.consent_label' => 'Consent checkbox text',
    ];

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEditSettingFields(): bool
    {
        return auth()->user()?->can('update', Setting::class) ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema(fn (?Page $record) => $record ? self::schemaFor($record->key) : []);
    }

    /**
     * @return array<int, Forms\Components\Component>
     */
    public static function schemaFor(PageKey $key): array
    {
        $isNotFound = $key === PageKey::NotFound;

        $schema = [
            Forms\Components\Section::make(__('Page header'))
                ->description(__('Leave a text empty to keep the original wording, shown in grey.'))
                ->schema([
                    SiteTextFields::make($key->titleKey(), __('Title')),
                    SiteTextFields::make($key->subtitleKey(), __('Subtitle'), multiline: true),
                    ImageUpload::make('banner', 'banner', '1920×640', '3:1')
                        ->label(__('Banner image'))
                        ->hidden($isNotFound || in_array($key, [PageKey::Privacy, PageKey::Terms], true)),
                ]),
        ];

        if ($key === PageKey::About) {
            $schema[] = Forms\Components\Section::make(__('About texts'))
                ->description(__('Introduction, vision and mission. The team is managed in Team Members.'))
                ->visible(self::canEditSettingFields())
                ->schema([
                    Forms\Components\Grid::make(2)->schema(array_map(
                        fn (string $field) => Forms\Components\Textarea::make('setting.'.$field)
                            ->label(__(str($field)->beforeLast('_')->headline()->toString().' ('.strtoupper(str($field)->afterLast('_')).')'))
                            ->rows(3)
                            ->extraInputAttributes(['dir' => str_ends_with($field, '_ar') ? 'rtl' : 'ltr']),
                        self::SETTING_FIELDS['about'],
                    )),
                ]);
        }

        if ($key === PageKey::Contact) {
            $schema[] = Forms\Components\Section::make(__('Contact form'))
                ->description(__('Name and phone are always shown and required.'))
                ->schema([
                    Forms\Components\Grid::make(2)->schema(collect(ContactForm::FIELDS)->flatMap(fn (array $defaults, string $field) => [
                        OptionToggle::make("options.fields.{$field}.show", $defaults[0])
                            ->label(__('Show “:field”', ['field' => trans(self::fieldLabelKey($field))]))
                            ->live(),
                        OptionToggle::make("options.fields.{$field}.required", $defaults[1])
                            ->label(__('Required'))
                            ->disabled(fn (Forms\Get $get) => ! $get("options.fields.{$field}.show")),
                    ])->all()),
                    ...collect(self::CONTACT_TEXTS)->map(fn (string $label, string $textKey) => SiteTextFields::make($textKey, __($label), multiline: true))->values()->all(),
                ]);
        }

        if (in_array($key, [PageKey::Privacy, PageKey::Terms], true)) {
            $fields = self::SETTING_FIELDS[$key->value];
            $schema[] = Forms\Components\Section::make(__('Page text'))
                ->description(__('Formatting, links and lists are kept; scripts and styles are removed when shown.'))
                ->visible(self::canEditSettingFields())
                ->schema(array_map(
                    fn (string $field) => Forms\Components\RichEditor::make('setting.'.$field)
                        ->label(str_ends_with($field, '_ar') ? __('Arabic') : __('English'))
                        ->disableToolbarButtons(['attachFiles', 'codeBlock'])
                        ->extraInputAttributes(['dir' => str_ends_with($field, '_ar') ? 'rtl' : 'ltr']),
                    $fields,
                ));
        }

        if (! $isNotFound) {
            $schema[] = Forms\Components\Section::make(__('Content blocks'))
                ->description(__('Optional sections shown at the end of the page, in this order.'))
                ->collapsible()
                ->schema([self::blocks()]);

            $seo = SeoFields::section();
            $seo->schema([
                ...$seo->getChildComponents(),
                Forms\Components\Toggle::make('noindex')
                    ->label(__('Hide this page from search engines'))
                    ->helperText(__('Adds “noindex”. Leave off unless the page should not appear in Google.'))
                    ->hidden($key->alwaysNoindex()),
                ImageUpload::make('og_image', 'og_image', '1200×630', '1.91:1')->label(__('Share image')),
            ]);
            $schema[] = $seo;
        }

        return $schema;
    }

    /** Public form label of a contact field (site.contact.form_*). */
    private static function fieldLabelKey(string $field): string
    {
        return 'site.contact.form_'.match ($field) {
            'service_id' => 'service',
            'stone_type_id' => 'stone_type',
            default => $field,
        };
    }

    private static function blocks(): Forms\Components\Builder
    {
        $pair = fn (string $name, string $label, string $type = 'text') => Forms\Components\Grid::make(2)->schema(array_map(
            fn (string $locale) => (match ($type) {
                'rich' => Forms\Components\RichEditor::make("{$name}_{$locale}")->disableToolbarButtons(['attachFiles', 'codeBlock']),
                'area' => Forms\Components\Textarea::make("{$name}_{$locale}")->rows(3),
                default => Forms\Components\TextInput::make("{$name}_{$locale}")->maxLength(200),
            })->label(__($label).' ('.strtoupper($locale).')')->extraInputAttributes(['dir' => $locale === 'ar' ? 'rtl' : 'ltr']),
            ['ar', 'en'],
        ));
        $image = fn (string $name, bool $multiple = false) => Forms\Components\FileUpload::make($name)
            ->label(__($multiple ? 'Images' : 'Image'))
            ->disk(PageBlocks::DISK)
            ->directory(PageBlocks::DIRECTORY)
            ->image()
            ->acceptedFileTypes(ImageUpload::MIME_TYPES)
            ->maxSize(ImageUpload::maxKb())
            ->multiple($multiple)
            ->reorderable($multiple)
            ->maxFiles($multiple ? 12 : null)
            ->helperText(__('JPG, PNG or WebP, up to :size MB.', ['size' => round(ImageUpload::maxKb() / 1024)]));

        return Forms\Components\Builder::make('blocks')
            ->label(__('Content blocks'))
            ->hiddenLabel()
            ->blocks([
                Forms\Components\Builder\Block::make('rich_text')->label(__('Text'))->icon('heroicon-o-bars-3-bottom-left')
                    ->schema([$pair('body', 'Text', 'rich')]),
                Forms\Components\Builder\Block::make('image_text')->label(__('Image and text'))->icon('heroicon-o-photo')
                    ->schema([
                        $pair('title', 'Title'),
                        $pair('text', 'Text', 'rich'),
                        $image('image'),
                        $pair('alt', 'Image description'),
                        Forms\Components\Select::make('image_position')->label(__('Image side'))
                            ->options(['start' => __('Before the text'), 'end' => __('After the text')])->default('start'),
                    ]),
                Forms\Components\Builder\Block::make('gallery')->label(__('Gallery'))->icon('heroicon-o-squares-2x2')
                    ->schema([$pair('title', 'Title'), $image('images', multiple: true)]),
                Forms\Components\Builder\Block::make('cta')->label(__('Call to action'))->icon('heroicon-o-megaphone')
                    ->schema([
                        $pair('title', 'Title'),
                        $pair('text', 'Text', 'area'),
                        $pair('button_label', 'Button text'),
                        Forms\Components\TextInput::make('button_link')->label(__('Button link'))->placeholder('/contact')
                            ->regex(MenuResource::URL_PATTERN)->extraInputAttributes(['dir' => 'ltr'])->maxLength(500),
                    ]),
                Forms\Components\Builder\Block::make('quote')->label(__('Quote'))->icon('heroicon-o-chat-bubble-bottom-center-text')
                    ->schema([$pair('quote', 'Quote', 'area'), $pair('author', 'Author')]),
            ])
            ->collapsible()
            ->cloneable()
            ->reorderableWithDragAndDrop()
            ->addActionLabel(__('Add block'))
            ->blockNumbers(false);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')->label(__('Page'))->searchable(false),
                Tables\Columns\TextColumn::make('blocks')->label(__('Content blocks'))
                    ->state(fn (Page $record) => count($record->blocks ?? [])),
                Tables\Columns\IconColumn::make('noindex')->label(__('Hidden from search engines'))->boolean(),
                Tables\Columns\TextColumn::make('updated_at')->label(__('Updated'))->since(),
            ])
            ->actions([
                Tables\Actions\Action::make('viewOnSite')
                    ->label(__('View on site'))
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Page $record) => $record->publicUrl(), shouldOpenInNewTab: true)
                    ->visible(fn (Page $record) => $record->publicUrl() !== null),
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('id')
            ->paginated(false);
    }

    public static function missingAction(): Action
    {
        return Action::make('addMissing')
            ->label(__('Add missing pages'))
            ->icon('heroicon-o-plus')
            ->visible(fn () => Page::query()->count() < count(PageKey::cases()) && (auth()->user()?->can('update', Page::class) ?? false))
            ->action(function () {
                app(ImportSiteContent::class)->pages();
                Notification::make()->title(__('Pages added'))->success()->send();
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPages::route('/'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}
