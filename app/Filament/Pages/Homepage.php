<?php

namespace App\Filament\Pages;

use App\Enums\AdminNavigationGroup;
use App\Filament\Pages\Concerns\EditsSiteSettings;
use App\Filament\Resources\MenuResource;
use App\Filament\Resources\SliderResource;
use App\Filament\Support\OptionToggle;
use App\Filament\Support\SiteTextFields;
use App\Models\SiteImage;
use App\Models\SiteText;
use App\Support\HomeSections;
use Filament\Forms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Get;
use Filament\Pages\Page;
use Illuminate\Support\HtmlString;

/**
 * The home page: section order and visibility, hero, section texts and the
 * repeated cards (why us, process, heritage). Stored in settings.home_sections;
 * section titles are site texts.
 */
class Homepage extends Page implements HasForms
{
    use EditsSiteSettings;

    protected static string $view = 'filament.pages.site-settings-form';

    protected static ?int $navigationSort = 10;

    /** Section => its title/subtitle site-text keys. */
    private const SECTION_TEXTS = [
        'why' => ['site.home.why_choose_us', 'site.home.why_choose_us_subtitle'],
        'services' => ['site.home.our_services', 'site.home.our_services_subtitle'],
        'categories' => ['site.home.categories_title', 'site.home.categories_subtitle'],
        'projects' => ['site.home.featured_projects'],
        'stones' => ['site.home.stone_collection'],
        'process' => ['site.process.title', 'site.process.subtitle'],
        'stats' => ['site.home.stats_title'],
        'heritage' => ['site.home.heritage_title', 'site.home.heritage_subtitle', 'site.home.heritage_note'],
        'testimonials' => ['site.home.testimonials'],
        'faq' => ['site.home.faq'],
        'cta' => ['site.home.cta_title', 'site.home.cta_subtitle'],
    ];

    /** List sections whose number of records can be limited. */
    private const COUNTED = ['services', 'projects', 'stones', 'faq'];

    public static function canAccess(): bool
    {
        return auth()->user()?->can('update', SiteText::class) ?? false;
    }

    public static function getNavigationGroup(): ?string
    {
        return AdminNavigationGroup::WebsiteContent->value;
    }

    public static function getNavigationLabel(): string
    {
        return __('Homepage');
    }

    public function getTitle(): string
    {
        return __('Homepage');
    }

    /**
     * @return list<string>
     */
    protected function siteTextKeys(): array
    {
        return ['site.home.hero_title', 'site.home.hero_subtitle', ...array_merge(...array_values(self::SECTION_TEXTS))];
    }

    private static function sectionLabel(string $section): string
    {
        return __('admin.home_sections.'.$section);
    }

    protected function schema(): array
    {
        $bilingual = fn (string $name, string $label, bool $area = false) => Forms\Components\Grid::make(2)->schema(array_map(
            fn (string $locale) => ($area ? Forms\Components\Textarea::make("{$name}_{$locale}")->rows(2) : Forms\Components\TextInput::make("{$name}_{$locale}")->maxLength(160))
                ->label(__($label).' ('.strtoupper($locale).')')
                ->required($locale === 'ar' && ! $area)
                ->extraInputAttributes(['dir' => $locale === 'ar' ? 'rtl' : 'ltr']),
            ['ar', 'en'],
        ));

        $texts = fn (string $section) => array_map(
            fn (string $key) => SiteTextFields::make($key, __(str($key)->contains('subtitle') ? 'Subtitle' : (str($key)->contains('note') ? 'Note' : 'Title')), multiline: str($key)->contains(['subtitle', 'note'])),
            self::SECTION_TEXTS[$section],
        );

        $section = fn (string $key, array $extra = []) => Forms\Components\Section::make(self::sectionLabel($key))
            ->collapsible()
            ->collapsed()
            ->schema([...$texts($key), ...$extra]);

        return [
            Forms\Components\Section::make(__('Sections'))
                ->description(__('Drag to change the order; switch a section off to hide it.'))
                ->schema([
                    Forms\Components\Repeater::make('layout')
                        ->hiddenLabel()
                        ->schema([
                            Forms\Components\Hidden::make('section'),
                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\Toggle::make('enabled')->label(__('Shown')),
                                Forms\Components\TextInput::make('count')
                                    ->label(__('How many'))
                                    ->numeric()->minValue(1)->maxValue(24)
                                    ->placeholder(fn (Get $get) => (string) (HomeSections::count((string) $get('section')) ?? __('All')))
                                    ->visible(fn (Get $get) => in_array($get('section'), self::COUNTED, true)),
                            ]),
                        ])
                        ->itemLabel(fn (array $state) => self::sectionLabel((string) ($state['section'] ?? '')))
                        ->addable(false)
                        ->deletable(false)
                        ->reorderableWithDragAndDrop()
                        ->collapsible(),
                ]),

            Forms\Components\Section::make(__('Hero'))
                ->description(new HtmlString(e(__('The slides themselves are managed in')).' <a class="underline" href="'.e(SliderResource::getUrl()).'">'.e(__('Sliders')).'</a>.'))
                ->collapsible()
                ->schema([
                    SiteTextFields::make('site.home.hero_title', __('Heading when there are no slides')),
                    SiteTextFields::make('site.home.hero_subtitle', __('Text when there are no slides'), multiline: true),
                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\TextInput::make('home_sections.hero.cta_label_ar')->label(__('Button text (AR)'))->placeholder(fn () => __('site.common.request_quote', [], 'ar'))->maxLength(40),
                        Forms\Components\TextInput::make('home_sections.hero.cta_label_en')->label(__('Button text (EN)'))->placeholder(fn () => __('site.common.request_quote', [], 'en'))->maxLength(40),
                        Forms\Components\TextInput::make('home_sections.hero.cta_link')->label(__('Button link'))->placeholder('/contact')
                            ->regex(MenuResource::URL_PATTERN)->extraInputAttributes(['dir' => 'ltr'])->maxLength(500),
                    ]),
                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\Select::make('home_sections.hero.overlay')->label(__('Darkening over the image'))
                            ->options(['light' => __('Light'), 'medium' => __('Medium (default)'), 'strong' => __('Strong')])
                            ->placeholder(__('Medium (default)'))
                            ->helperText(__('Stronger keeps white text readable on bright photos.')),
                        OptionToggle::make('home_sections.hero.video')->label(__('Play the marble video when there is no photo')),
                        OptionToggle::make('home_sections.hero.whatsapp')->label(__('Show the WhatsApp button')),
                    ]),
                ]),

            $section('why', [
                Forms\Components\Repeater::make('home_sections.sections.why.items')
                    ->label(__('Cards'))
                    ->schema([
                        Forms\Components\Select::make('icon')->label(__('Icon'))->required()
                            ->options(collect(HomeSections::ICONS)->mapWithKeys(fn (string $icon) => [$icon => str($icon)->after('heroicon-o-')->headline()->toString()])->all()),
                        $bilingual('title', 'Title'),
                        $bilingual('description', 'Text', area: true),
                    ])
                    ->itemLabel(fn (array $state) => $state['title_'.(app()->getLocale() === 'ar' ? 'ar' : 'en')] ?? null)
                    ->reorderableWithDragAndDrop()->collapsible()->collapsed()->maxItems(8)
                    ->addActionLabel(__('Add card')),
            ]),
            $section('services'),
            $section('categories'),
            $section('projects'),
            $section('stones'),
            $section('process', [
                Forms\Components\Repeater::make('home_sections.sections.process.items')
                    ->label(__('Steps'))
                    ->schema([$bilingual('title', 'Title'), $bilingual('description', 'Text', area: true)])
                    ->itemLabel(fn (array $state) => $state['title_'.(app()->getLocale() === 'ar' ? 'ar' : 'en')] ?? null)
                    ->reorderableWithDragAndDrop()->collapsible()->collapsed()->maxItems(8)
                    ->addActionLabel(__('Add step')),
            ]),
            $section('stats', [
                Forms\Components\Placeholder::make('stats_help')->hiddenLabel()
                    ->content(__('The figures themselves are entered in Settings → Statistics; the section stays hidden until at least one is set.')),
            ]),
            $section('heritage', [
                Forms\Components\Repeater::make('home_sections.sections.heritage.items')
                    ->label(__('Places'))
                    ->schema([
                        Forms\Components\Select::make('slot')->label(__('Image'))
                            ->options(fn () => collect(SiteImage::SLOTS)->keys()->filter(fn (string $slot) => str_starts_with($slot, 'heritage-'))
                                ->mapWithKeys(fn (string $slot) => [$slot => __('admin.site_image_slots.'.$slot.'.label')])->all())
                            ->helperText(__('Photos are replaced in Site images. Without a photo, a pattern card is shown.')),
                        $bilingual('name', 'Name'),
                        $bilingual('caption', 'Caption'),
                    ])
                    ->itemLabel(fn (array $state) => $state['name_'.(app()->getLocale() === 'ar' ? 'ar' : 'en')] ?? null)
                    ->reorderableWithDragAndDrop()->collapsible()->collapsed()->maxItems(8)
                    ->addActionLabel(__('Add place')),
            ]),
            $section('testimonials'),
            $section('faq'),
            $section('cta', [
                Forms\Components\Select::make('home_sections.sections.cta.background')->label(__('Background texture'))
                    ->options(collect(HomeSections::CTA_BACKGROUNDS)->mapWithKeys(fn (string $slot) => [$slot => __('admin.site_image_slots.'.$slot.'.label')])->all())
                    ->placeholder(__('admin.site_image_slots.texture-sandstone.label')),
            ]),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFillData(array $data): array
    {
        $saved = (array) ($data['home_sections'] ?? []);

        $data['layout'] = array_map(fn (string $section) => [
            'section' => $section,
            'enabled' => HomeSections::enabled($section),
            'count' => data_get($saved, "sections.{$section}.count"),
        ], array_values(array_unique([...array_intersect((array) ($saved['order'] ?? []), HomeSections::SECTIONS), ...HomeSections::SECTIONS])));

        // Start the repeaters from the cards shown today, so they can be edited in place.
        $defaults = [
            'why' => fn (string $locale) => array_values((array) __('site.home.why_items', [], $locale)),
            'process' => fn (string $locale) => array_values((array) __('site.process.steps', [], $locale)),
            'heritage' => fn (string $locale) => array_values((array) __('site.home.heritage_items', [], $locale)),
        ];
        $slots = array_map(fn ($key) => 'heritage-'.$key, array_keys((array) __('site.home.heritage_items', [], 'en')));
        foreach ($defaults as $section => $items) {
            if (! empty(data_get($saved, "sections.{$section}.items"))) {
                continue;
            }
            $ar = $items('ar');
            $en = $items('en');
            $rows = [];
            foreach ($en as $index => $item) {
                $row = [];
                foreach ($item as $field => $value) {
                    if ($field === 'icon') {
                        $row['icon'] = $value;

                        continue;
                    }
                    $row["{$field}_en"] = $value;
                    $row["{$field}_ar"] = $ar[$index][$field] ?? null;
                }
                if ($section === 'heritage') {
                    $row['slot'] = $slots[$index] ?? null;
                }
                $rows[] = $row;
            }
            data_set($data, "home_sections.sections.{$section}.items", $rows);
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>
     */
    protected function mutateSaveData(array $state): array
    {
        $home = (array) ($state['home_sections'] ?? []);
        $home['order'] = [];
        foreach (array_values((array) ($state['layout'] ?? [])) as $row) {
            $section = (string) ($row['section'] ?? '');
            if (! in_array($section, HomeSections::SECTIONS, true)) {
                continue;
            }
            $home['order'][] = $section;
            data_set($home, "sections.{$section}.enabled", (bool) ($row['enabled'] ?? true));
            data_set($home, "sections.{$section}.count", filled($row['count'] ?? null) ? (int) $row['count'] : null);
        }
        unset($state['layout']);

        return ['home_sections' => $home];
    }
}
