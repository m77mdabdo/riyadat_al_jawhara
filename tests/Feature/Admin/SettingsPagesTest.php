<?php

namespace Tests\Feature\Admin;

use App\Filament\Pages\Settings;
use App\Filament\Pages\Settings\AnalyticsSettings;
use App\Filament\Pages\Settings\ContactSettings;
use App\Filament\Pages\Settings\LegalSettings;
use App\Filament\Pages\Settings\NotificationSettings;
use App\Filament\Pages\Settings\SeoSettings;
use App\Filament\Pages\Settings\SocialSettings;
use App\Filament\Pages\Settings\StatisticsSettings;
use App\Models\Lead;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\NewLeadNotification;
use App\Support\FollowUpDigestTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * The Settings sub-pages: admins only, saving keeps every other value, and
 * each option reaches the site.
 */
class SettingsPagesTest extends TestCase
{
    use RefreshDatabase;

    private const PAGES = [
        Settings::class => '/admin/settings',
        ContactSettings::class => '/admin/settings/contact',
        SocialSettings::class => '/admin/settings/social',
        SeoSettings::class => '/admin/settings/seo',
        AnalyticsSettings::class => '/admin/settings/analytics',
        NotificationSettings::class => '/admin/settings/notifications',
        StatisticsSettings::class => '/admin/settings/statistics',
        LegalSettings::class => '/admin/settings/legal',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        Role::create(['name' => 'editor', 'guard_name' => 'web']);
    }

    private function actingAsRole(?string $role): void
    {
        $user = User::factory()->create();
        if ($role) {
            $user->assignRole($role);
        }
        $this->actingAs($user);
    }

    public function test_only_admins_open_the_settings_pages(): void
    {
        $this->actingAsRole('admin');
        foreach (self::PAGES as $url) {
            $this->get($url)->assertOk();
        }

        foreach (['editor', null] as $role) {
            $this->actingAsRole($role);
            foreach (self::PAGES as $page => $url) {
                $this->get($url)->assertForbidden();
                Livewire::test($page)->assertForbidden();
            }
        }
    }

    public function test_saving_each_page_keeps_every_other_value(): void
    {
        $values = [
            'phone' => '+966 11 000 0000', 'whatsapp' => '966500000000', 'email' => 'info@company.test',
            'address_ar' => 'الرياض', 'address_en' => 'Riyadh', 'map_lat' => '24.7136', 'map_lng' => '46.6753',
            'facebook_url' => 'https://facebook.com/example', 'about_en' => 'About us', 'privacy_policy_en' => '<p>Policy</p>',
            'stat_years_experience' => 15, 'lead_auto_reply' => true, 'comment_mail_notifications' => true,
        ];
        Setting::editable()->update($values);
        $before = Setting::current()->getAttributes();
        $this->actingAsRole('admin');

        foreach (array_keys(self::PAGES) as $page) {
            Livewire::test($page)->call('save')->assertHasNoErrors();
        }

        $after = Setting::current()->getAttributes();
        foreach (array_keys($values) as $column) {
            $this->assertEquals($before[$column], $after[$column], $column);
        }
        $html = $this->get(route('contact', ['locale' => 'en']))->getContent();
        $this->assertStringContainsString('maps.google.com/maps?q=24.7136,46.6753', $html);
        $this->assertStringContainsString(__('site.contact.get_directions', [], 'en'), $html);
    }

    public function test_map_and_directions_can_be_hidden(): void
    {
        Setting::editable()->update(['map_lat' => '24.7136', 'map_lng' => '46.6753']);
        $this->actingAsRole('admin');

        Livewire::test(ContactSettings::class)
            ->fillForm(['contact_options.show_directions' => false])
            ->call('save')
            ->assertHasNoErrors();

        $html = $this->get(route('contact', ['locale' => 'ar']))->getContent();
        $this->assertStringContainsString('maps.google.com/maps?q=', $html);
        $this->assertStringNotContainsString('google.com/maps/dir/', $html);
    }

    public function test_seo_pattern_noindex_and_verification(): void
    {
        $this->actingAsRole('admin');

        Livewire::test(SeoSettings::class)
            ->fillForm(['seo_options.title_pattern' => 'no page token'])
            ->call('save')
            ->assertHasErrors(['data.seo_options.title_pattern']);

        Livewire::test(SeoSettings::class)
            ->fillForm([
                'seo_options.title_pattern' => '{page} — {site}',
                'seo_options.noindex' => true,
                'seo_options.google_verification' => 'abcDEF123_-xyz',
                'seo_options.bing_verification' => '<script>',
            ])
            ->call('save')
            ->assertHasErrors(['data.seo_options.bing_verification']);

        Livewire::test(SeoSettings::class)
            ->fillForm([
                'seo_options.title_pattern' => '{page} — {site}',
                'seo_options.noindex' => true,
                'seo_options.google_verification' => 'abcDEF123_-xyz',
                'site_texts.site~meta~default_description.en' => 'Natural stone, supplied and installed.',
            ])
            ->call('save')
            ->assertHasNoErrors();

        $html = $this->get(route('faq', ['locale' => 'en']))->getContent();
        $this->assertStringContainsString('<title>'.__('site.faq.title', [], 'en').' — '.__('site.meta.site_name', [], 'en').'</title>', $html);
        $this->assertStringContainsString('<meta name="robots" content="noindex, nofollow">', $html);
        $this->assertStringContainsString('<meta name="google-site-verification" content="abcDEF123_-xyz">', $html);
        $this->assertStringContainsString('Natural stone, supplied and installed.', $this->get(route('home', ['locale' => 'en']))->getContent());
    }

    public function test_analytics_ids_are_validated_and_override_the_environment(): void
    {
        config(['analytics.ga4_id' => null, 'analytics.plausible_domain' => null]);
        $this->actingAsRole('admin');

        Livewire::test(AnalyticsSettings::class)
            ->fillForm(['analytics_options.ga4_id' => 'UA-12345'])
            ->call('save')
            ->assertHasErrors(['data.analytics_options.ga4_id']);

        Livewire::test(AnalyticsSettings::class)
            ->fillForm(['analytics_options.ga4_id' => 'G-ABC123XYZ'])
            ->call('save')
            ->assertHasNoErrors();

        $response = $this->get(route('home', ['locale' => 'en']));
        $this->assertStringContainsString('G-ABC123XYZ', $response->getContent());
        $this->assertStringContainsString('https://www.googletagmanager.com', $response->headers->get('Content-Security-Policy'));
        $this->assertStringContainsString(__('site.consent.accept', [], 'en'), $response->getContent());

        // "Off" wins over everything, including the .env value.
        config(['analytics.ga4_id' => 'G-FROMENV1']);
        Livewire::test(AnalyticsSettings::class)->fillForm(['analytics_options.disabled' => true])->call('save');
        $response = $this->get(route('home', ['locale' => 'en']));
        $this->assertStringNotContainsString('G-', $response->headers->get('Content-Security-Policy') ?? '');
        $this->assertStringNotContainsString('googletagmanager', $response->getContent());
    }

    public function test_extra_lead_recipients_and_digest_time(): void
    {
        Notification::fake();
        $this->actingAsRole('admin');

        Livewire::test(NotificationSettings::class)
            ->fillForm(['notification_options.lead_recipients' => ['not an email']])
            ->call('save')
            ->assertHasErrors();

        Livewire::test(NotificationSettings::class)
            ->fillForm([
                'notification_options.lead_recipients' => ['Sales@Company.test', 'sales@company.test', 'owner@company.test'],
                'notification_options.digest_time' => '07:30',
            ])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(['sales@company.test', 'owner@company.test'], Setting::current()->option('notification_options', 'lead_recipients'));
        $this->assertSame('07:30', FollowUpDigestTime::get());

        NewLeadNotification::dispatchFor(Lead::factory()->create());
        foreach (['sales@company.test', 'owner@company.test'] as $email) {
            Notification::assertSentOnDemand(NewLeadNotification::class, fn ($n, array $channels, AnonymousNotifiable $notifiable) => $notifiable->routes['mail'] === $email);
        }
    }

    public function test_digest_time_falls_back_to_the_config(): void
    {
        Setting::editable()->update(['notification_options' => ['digest_time' => '25:99']]);

        $this->assertSame(config('site.notifications.digest_time'), FollowUpDigestTime::get());
    }
}
