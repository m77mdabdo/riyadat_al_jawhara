<?php

namespace Tests\Feature\Admin;

use App\Enums\MenuLocation;
use App\Enums\PageKey;
use App\Filament\Pages\Branding;
use App\Filament\Pages\FloatingElements;
use App\Filament\Pages\FooterSettings;
use App\Filament\Pages\Homepage;
use App\Filament\Pages\SiteTexts;
use App\Filament\Resources\MenuResource\Pages\EditMenu;
use App\Filament\Resources\PageResource\Pages\EditPage;
use App\Filament\Resources\SiteImageResource\Pages\EditSiteImage;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\Project;
use App\Models\Setting;
use App\Models\SiteImage;
use App\Models\SiteText;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Website Content: everything falls back to the shipped site, the import
 * changes nothing visible and is idempotent, and each editable item shows on
 * the public site in both languages right after saving.
 */
class WebsiteContentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        Role::create(['name' => 'editor', 'guard_name' => 'web']);
        $this->seed(DemoSeeder::class);
        Setting::editable()->update(['whatsapp' => '966500000000']);
    }

    private function actingAsRole(?string $role): User
    {
        $user = User::factory()->create();
        if ($role) {
            $user->assignRole($role);
        }
        $this->actingAs($user);

        return $user;
    }

    /** Page HTML without the per-request parts (CSRF token, CSP nonces, random SVG pattern ids). */
    private function html(string $url): string
    {
        $html = $this->get($url)->assertOk()->getContent();

        return preg_replace(['/<meta name="csrf-token" content="[^"]*">/', '/nonce="[^"]*"/', '/name="_token" value="[^"]*"/', '/\b(geo|najdi)-[A-Za-z0-9]{6}\b/'], ['', '', '', '$1-id'], $html);
    }

    /**
     * @return array<string, string>
     */
    private function snapshot(): array
    {
        $pages = [];
        foreach (['ar', 'en'] as $locale) {
            foreach (['home', 'services.index', 'stones.index', 'about', 'contact', 'faq', 'legal.privacy', 'blog.index'] as $route) {
                $pages["{$locale}:{$route}"] = $this->html(route($route, ['locale' => $locale]));
            }
        }

        return $pages;
    }

    public function test_the_import_changes_nothing_visible_and_is_idempotent(): void
    {
        $before = $this->snapshot();

        $this->artisan('app:import-site-content')->assertSuccessful();

        $this->assertSame(count(MenuLocation::cases()), Menu::count());
        $this->assertSame(count(PageKey::cases()), Page::count());
        $this->assertSame(count(SiteImage::SLOTS), SiteImage::count());
        $items = MenuItem::count();
        $this->assertGreaterThan(0, $items);

        foreach ($this->snapshot() as $page => $html) {
            $this->assertSame($before[$page], $html, "{$page} changed after the import");
        }

        // Second run: nothing new, nothing changed.
        MenuItem::query()->first()->update(['label_en' => 'Edited by the owner']);
        $this->artisan('app:import-site-content')->assertSuccessful();
        $this->assertSame($items, MenuItem::count());
        $this->assertSame(count(PageKey::cases()), Page::count());
        $this->assertDatabaseHas('menu_items', ['label_en' => 'Edited by the owner']);
    }

    public function test_saving_untouched_admin_pages_changes_nothing(): void
    {
        $this->artisan('app:import-site-content');
        $before = $this->snapshot();
        $this->actingAsRole('admin');

        foreach ([Homepage::class, FooterSettings::class, FloatingElements::class, Branding::class] as $page) {
            Livewire::test($page)->call('save')->assertHasNoErrors();
        }
        foreach (Menu::all() as $menu) {
            Livewire::test(EditMenu::class, ['record' => $menu->getRouteKey()])->call('save')->assertHasNoErrors();
        }
        foreach (Page::all() as $page) {
            Livewire::test(EditPage::class, ['record' => $page->getRouteKey()])->call('save')->assertHasNoErrors();
        }

        auth()->logout();
        foreach ($this->snapshot() as $page => $html) {
            $this->assertSame($before[$page], $html, "{$page} changed after saving untouched admin pages");
        }
    }

    public function test_site_texts_override_both_languages_and_refresh_cached_pages(): void
    {
        $this->html(route('home', ['locale' => 'en'])); // cache the home sections
        $this->actingAsRole('editor');

        Livewire::test(SiteTexts::class)
            ->set('filters.search', 'home.cta_title')
            ->set('values.site~home~cta_title.ar', 'جاهز لمشروعك القادم؟')
            ->set('values.site~home~cta_title.en', 'Ready for your next project?')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertStringContainsString('Ready for your next project?', $this->html(route('home', ['locale' => 'en'])));
        $this->assertStringContainsString('جاهز لمشروعك القادم؟', $this->html(route('home', ['locale' => 'ar'])));

        // Restoring brings back the shipped text.
        Livewire::test(SiteTexts::class)->call('resetKey', 'site~home~cta_title');
        $this->assertSame(0, SiteText::count());
        $this->assertStringContainsString(e(__('site.home.cta_title', [], 'en')), $this->html(route('home', ['locale' => 'en'])));
    }

    public function test_site_texts_keep_their_placeholders(): void
    {
        $this->actingAsRole('editor');

        Livewire::test(SiteTexts::class)
            ->set('filters.group', 'home')
            ->set('filters.search', 'categories_cta')
            ->set('values.site~home~categories_cta.en', 'Browse the collection')
            ->call('save')
            ->assertHasErrors(['values.site~home~categories_cta.en']);

        $this->assertSame(0, SiteText::count());
    }

    public function test_menus_header_options_and_items_show_on_the_site(): void
    {
        $this->artisan('app:import-site-content');
        $this->actingAsRole('editor');
        $header = Menu::query()->where('location', 'header')->sole();

        Livewire::test(EditMenu::class, ['record' => $header->getRouteKey()])
            ->fillForm([
                'options.cta_show' => true,
                'options.cta_label_ar' => 'اطلب زيارة',
                'options.cta_label_en' => 'Book a visit',
                'options.cta_link' => '/projects',
                'options.show_search' => false,
            ])
            ->call('save')
            ->assertHasNoErrors();

        $project = Project::query()->where('is_active', true)->first();
        $header->allItems()->create(['label_ar' => 'مشروع مميز', 'label_en' => 'Signature project', 'link_type' => 'record', 'linkable_type' => $project->getMorphClass(), 'linkable_id' => $project->id, 'order' => 99]);
        $header->allItems()->where('route_name', 'gallery')->update(['is_active' => false]);
        Menu::query()->where('location', 'header')->first()->touch();

        $en = $this->html(route('home', ['locale' => 'en']));
        $this->assertStringContainsString('Book a visit', $en);
        $this->assertStringContainsString(route('projects.index', ['locale' => 'en']), $en);
        $this->assertStringContainsString('Signature project', $en);
        $this->assertStringContainsString(route('projects.show', ['locale' => 'en', 'project' => $project]), $en);
        $this->assertStringNotContainsString('aria-label="'.__('site.search.title', [], 'en').'"', $en);
        $ar = $this->html(route('home', ['locale' => 'ar']));
        $this->assertStringContainsString('اطلب زيارة', $ar);
        $this->assertStringContainsString('مشروع مميز', $ar);

        // A hidden record drops out of the menu.
        $project->update(['is_active' => false]);
        $this->assertStringNotContainsString('Signature project', $this->html(route('home', ['locale' => 'en'])));
    }

    public function test_footer_columns_come_from_the_footer_menu(): void
    {
        $this->artisan('app:import-site-content');
        $footer = Menu::query()->where('location', 'footer')->sole();
        $column = $footer->allItems()->create(['label_ar' => 'الأحجار', 'label_en' => 'Our stones', 'link_type' => 'url', 'url' => '#', 'order' => 50]);
        $footer->allItems()->create(['parent_id' => $column->id, 'label_ar' => 'رخام', 'label_en' => 'Marble', 'link_type' => 'url', 'url' => '/stones?stone_category=marble', 'order' => 1]);

        $html = $this->html(route('home', ['locale' => 'en']));
        $this->assertStringContainsString('>Our stones</h2>', $html);
        $this->assertStringContainsString(url('/en/stones?stone_category=marble'), $html);
        $this->assertStringContainsString('>'.__('site.footer.quick_links', [], 'en').'</h2>', $html);
    }

    public function test_homepage_sections_order_visibility_and_cards(): void
    {
        $this->html(route('home', ['locale' => 'en']));
        $this->actingAsRole('editor');

        $component = Livewire::test(Homepage::class);
        $layout = collect($component->get('data.layout'))->map(function (array $row) {
            if ($row['section'] === 'faq') {
                $row['enabled'] = false;
            }
            if ($row['section'] === 'stones') {
                $row['count'] = 2;
            }

            return $row;
        })->all();
        $why = $component->get('data.home_sections.sections.why.items');
        $first = array_key_first($why);
        $why[$first]['title_en'] = 'Craft you can measure';
        $why[$first]['title_ar'] = 'حرفية يمكن قياسها';

        $component->set('data.layout', $layout)
            ->set('data.home_sections.sections.why.items', $why)
            ->set('data.home_sections.hero.overlay', 'strong')
            ->call('save')
            ->assertHasNoErrors();

        $en = $this->html(route('home', ['locale' => 'en']));
        $this->assertStringContainsString('Craft you can measure', $en);
        $this->assertStringNotContainsString('home-faq-', $en);
        $this->assertStringContainsString('from-charcoal/95', $en);
        $this->assertSame(2, substr_count($en, 'href="'.url('/en/stones').'/'));
        $this->assertStringContainsString('حرفية يمكن قياسها', $this->html(route('home', ['locale' => 'ar'])));
    }

    public function test_pages_header_seo_noindex_and_blocks(): void
    {
        $this->artisan('app:import-site-content');
        $this->actingAsRole('editor');
        $page = Page::query()->where('key', 'services')->sole();

        Livewire::test(EditPage::class, ['record' => $page->getRouteKey()])
            ->fillForm([
                'site_texts.site~services~title.en' => 'Stone services',
                'site_texts.site~services~title.ar' => 'خدمات الحجر',
                'meta_title_en' => 'Stone services in Riyadh',
                'noindex' => true,
                'blocks' => [
                    ['type' => 'quote', 'data' => ['quote_en' => 'Stone outlives us all.', 'quote_ar' => 'الحجر يبقى.', 'author_en' => 'A mason', 'author_ar' => 'بنّاء']],
                    ['type' => 'rich_text', 'data' => ['body_en' => '<p>Safe <strong>text</strong></p><script>alert(1)</script>', 'body_ar' => '<p>نص</p>']],
                ],
            ])
            ->call('save')
            ->assertHasNoErrors();

        $en = $this->html(route('services.index', ['locale' => 'en']));
        $this->assertStringContainsString('>Stone services</h1>', $en);
        $this->assertStringContainsString('<title>Stone services in Riyadh |', $en);
        $this->assertStringContainsString('<meta name="robots" content="noindex, follow">', $en);
        $this->assertStringContainsString('Stone outlives us all.', $en);
        $this->assertStringContainsString('<strong>text</strong>', $en);
        $this->assertStringNotContainsString('alert(1)', $en);
        $ar = $this->html(route('services.index', ['locale' => 'ar']));
        $this->assertStringContainsString('>خدمات الحجر</h1>', $ar);
        $this->assertStringContainsString('الحجر يبقى.', $ar);
    }

    public function test_contact_form_fields_follow_the_contact_page(): void
    {
        $this->artisan('app:import-site-content');
        $this->actingAsRole('editor');
        $page = Page::query()->where('key', 'contact')->sole();

        Livewire::test(EditPage::class, ['record' => $page->getRouteKey()])
            ->fillForm([
                'options.fields.city.show' => false,
                'options.fields.email.required' => true,
                'options.fields.message.required' => false,
            ])
            ->call('save')
            ->assertHasNoErrors();

        auth()->logout();
        $form = $this->html(route('contact', ['locale' => 'en']));
        $this->assertStringNotContainsString('name="city"', $form);
        $this->assertStringContainsString('name="email"', $form);
        $this->assertStringNotContainsString(__('site.contact.form_email', [], 'en'), $form, 'a required field is not labelled optional');
        $this->assertMatchesRegularExpression('/name="email" id="email"\s+required/', $form);

        $base = ['name' => 'Sara', 'phone' => '0500000000', 'consent' => '1'];
        $this->post(route('contact.store', ['locale' => 'en']), $base)->assertSessionHasErrors('email');
        $this->post(route('contact.store', ['locale' => 'en']), $base + ['email' => 'sara@example.com', 'city' => 'Jeddah'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('leads', ['email' => 'sara@example.com', 'city' => null, 'message' => null]);
    }

    public function test_site_image_upload_replaces_and_resets_a_slot(): void
    {
        $this->artisan('app:import-site-content');
        $this->actingAsRole('editor');
        $slot = SiteImage::query()->where('slot', 'texture-quartz')->sole();

        Livewire::test(EditSiteImage::class, ['record' => $slot->getRouteKey()])
            ->set('data.image', [UploadedFile::fake()->image('quartz.jpg', 1280, 853)])
            ->fillForm(['alt_en' => 'White quartz'])
            ->call('save')
            ->assertHasNoErrors();

        $url = $slot->fresh()->getFirstMedia('image')->getUrl('large');
        $this->assertStringContainsString($url, $this->html(route('faq', ['locale' => 'en'])));

        $slot->fresh()->clearMediaCollection('image');
        $this->assertStringNotContainsString($url, $this->html(route('faq', ['locale' => 'en'])));
    }

    public function test_footer_and_floating_elements(): void
    {
        $this->actingAsRole('editor');

        Livewire::test(FooterSettings::class)
            ->fillForm([
                'footer_options.show_social' => false,
                'footer_options.copyright_en' => '© {year} Riyadat Al-Jawhara. Crafted in Riyadh.',
                'site_texts.site~footer~about.en' => 'Stone, carefully chosen.',
            ])
            ->call('save')
            ->assertHasNoErrors();

        Livewire::test(FloatingElements::class)
            ->fillForm([
                'floating_options.whatsapp_position' => 'start',
                'floating_options.back_to_top' => false,
                'site_texts.site~whatsapp_tooltip.en' => 'Questions? We reply fast.',
            ])
            ->call('save')
            ->assertHasNoErrors();

        $en = $this->html(route('home', ['locale' => 'en']));
        $this->assertStringContainsString('© '.now()->year.' Riyadat Al-Jawhara. Crafted in Riyadh.', $en);
        $this->assertStringContainsString('Stone, carefully chosen.', $en);
        $this->assertStringNotContainsString(__('site.footer.follow_us', [], 'en'), $en);
        $this->assertStringContainsString('Questions? We reply fast.', $en);
        $this->assertStringContainsString('left: calc(1rem + env(safe-area-inset-left))', $en);
        $this->assertStringNotContainsString('aria-label="'.__('site.common.back_to_top', [], 'en').'"', $en);
        // Start = right in Arabic.
        $this->assertStringContainsString('right: calc(1rem + env(safe-area-inset-right))', $this->html(route('home', ['locale' => 'ar'])));
    }

    public function test_branding_name_and_favicon(): void
    {
        $this->actingAsRole('admin');

        Livewire::test(Branding::class)
            ->set('data.favicon', [UploadedFile::fake()->image('icon.png', 512, 512)])
            ->fillForm(['site_texts.site~meta~site_name.en' => 'Jawhara Stone'])
            ->call('save')
            ->assertHasNoErrors();

        $html = $this->html(route('about', ['locale' => 'en']));
        $this->assertStringContainsString('| Jawhara Stone</title>', $html);
        $this->assertStringContainsString(Setting::current()->faviconUrl(32), $html);
        $this->assertStringContainsString(__('site.meta.site_name', [], 'ar'), $this->html(route('about', ['locale' => 'ar'])));
    }

    public function test_access_by_role(): void
    {
        $this->artisan('app:import-site-content');
        $menu = Menu::first();
        $page = Page::first();
        $image = SiteImage::first();
        $urls = [
            'content' => ['/admin/menus', "/admin/menus/{$menu->id}/edit", '/admin/pages', "/admin/pages/{$page->id}/edit", '/admin/site-images', "/admin/site-images/{$image->id}/edit", '/admin/site-texts', '/admin/homepage', '/admin/footer', '/admin/floating-elements'],
            'admin' => ['/admin/branding'],
        ];

        $this->actingAsRole('admin');
        foreach ([...$urls['content'], ...$urls['admin']] as $url) {
            $this->get($url)->assertOk();
        }

        $this->actingAsRole('editor');
        foreach ($urls['content'] as $url) {
            $this->get($url)->assertOk();
        }
        $this->get('/admin/branding')->assertForbidden();

        $this->actingAsRole(null);
        foreach ([...$urls['content'], ...$urls['admin']] as $url) {
            $this->get($url)->assertForbidden();
        }
    }

    public function test_editors_do_not_see_the_settings_backed_legal_text(): void
    {
        $this->artisan('app:import-site-content');
        $privacy = Page::query()->where('key', 'privacy')->sole();
        Setting::editable()->update(['privacy_policy_en' => '<p>Original policy</p>']);

        $this->actingAsRole('editor');
        Livewire::test(EditPage::class, ['record' => $privacy->getRouteKey()])
            ->assertFormFieldIsHidden('setting.privacy_policy_en')
            ->call('save')
            ->assertHasNoErrors();
        $this->assertSame('<p>Original policy</p>', Setting::current()->privacy_policy_en);

        $this->actingAsRole('admin');
        Livewire::test(EditPage::class, ['record' => $privacy->getRouteKey()])
            ->assertFormFieldIsVisible('setting.privacy_policy_en')
            ->fillForm(['setting.privacy_policy_en' => '<p>Updated policy</p>'])
            ->call('save')
            ->assertHasNoErrors();
        $this->assertStringContainsString('Updated policy', $this->html(route('legal.privacy', ['locale' => 'en'])));
    }
}
