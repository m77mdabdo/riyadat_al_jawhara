<?php

namespace App\Providers;

use App\Contracts\CatalogueGenerator;
use App\Enums\UserRole;
use App\Models\BlogCategory;
use App\Models\Faq;
use App\Models\Post;
use App\Models\Project;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Slider;
use App\Models\StoneAttribute;
use App\Models\StoneCategory;
use App\Models\StoneType;
use App\Models\Tag;
use App\Models\TeamMember;
use App\Models\Testimonial;
use App\Models\User;
use App\Notifications\Mail\PasswordResetMail;
use App\Observers\PublicContentObserver;
use App\Policies\ActivityPolicy;
use App\Services\Catalogue\MpdfCatalogueGenerator;
use App\Services\StockCandidateFinder;
use App\Services\StockMedia\PexelsProvider;
use App\Services\StockMedia\PixabayProvider;
use App\Services\StockMedia\UnsplashProvider;
use App\Services\StockMediaFetcher;
use App\View\Composers\NavigationComposer;
use App\View\Composers\SiteSettingsComposer;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Spatie\Activitylog\Models\Activity;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // One Setting instance per request (reset between requests, queue jobs
        // and tests), backed by the cache -- see Setting::current().
        $this->app->scoped(Setting::CACHE_KEY, fn () => Setting::loadCurrent());

        $this->app->singleton(CatalogueGenerator::class, MpdfCatalogueGenerator::class);

        // Stock-photo APIs (demo/content tooling only).
        $this->app->tag([PexelsProvider::class, PixabayProvider::class, UnsplashProvider::class], 'stock-media.providers');
        $this->app->when(StockMediaFetcher::class)->needs('$providers')->giveTagged('stock-media.providers');
        $this->app->when(StockCandidateFinder::class)->needs('$providers')
            ->give(fn ($app) => [$app->make(PexelsProvider::class), $app->make(PixabayProvider::class)]);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Default {locale} for URLs generated outside a public request (CLI,
        // queued mail, sitemap); SetLocale overrides it per request.
        URL::defaults(['locale' => config('locales.default')]);

        // Outside production, N+1 queries and fills of non-fillable attributes
        // fail loudly instead of passing silently.
        Model::preventLazyLoading(! $this->app->isProduction());
        Model::preventSilentlyDiscardingAttributes(! $this->app->isProduction());

        // Cached public output (home sections, sitemap, catalogue PDFs) is
        // invalidated by one observer on every model the public site shows.
        foreach ([
            Service::class, StoneType::class, StoneCategory::class, StoneAttribute::class, Project::class,
            Post::class, BlogCategory::class, Tag::class, Faq::class, Slider::class, Testimonial::class,
            TeamMember::class, Setting::class, Media::class,
        ] as $model) {
            $model::observe(PublicContentObserver::class);
        }

        View::composer([
            'components.layout', 'partials.header', 'partials.footer',
            'components.whatsapp-float', 'components.error-page', 'pdf.catalogue',
            'mail::message', 'mail::header', 'mail.*',
        ], SiteSettingsComposer::class);
        View::composer(['partials.header', 'partials.footer'], NavigationComposer::class);

        // Authorization lives in app/Policies; admins may do everything.
        Gate::before(fn (User $user): ?bool => $user->hasRole(UserRole::Admin->value) ? true : null);
        Gate::policy(Activity::class, ActivityPolicy::class);

        // Branded, localized admin password-reset email (Filament's own
        // ResetPassword notification stays in place; only the content changes).
        ResetPassword::toMailUsing(fn (object $user, string $token) => PasswordResetMail::build(
            filament()->getPanel('admin')->getResetPasswordUrl($token, $user)
        ));

        // One password policy for every place a password is set: profile page,
        // password reset, and app:create-admin.
        Password::defaults(fn () => Password::min(12)->letters()->numbers());

        // Named limiters (config/site.php), each with its own per-IP counter.
        // Unnamed "throttle:N,1" middleware would share one counter across
        // routes, so browsing search could block the comment form.
        foreach (['comments', 'beacons', 'catalogue', 'search'] as $name) {
            RateLimiter::for($name, fn (Request $request) => Limit::perMinute((int) config("site.throttle.{$name}"))->by($name.'|'.$request->ip()));
        }

        // Public lead form: a throttled visitor is sent back to the form with
        // their input intact and a translated message instead of the bare 429 page.
        RateLimiter::for('contact', function (Request $request) {
            return Limit::perMinute((int) config('site.throttle.contact'))
                ->by('contact|'.$request->ip())
                ->response(fn () => back()
                    ->withInput($request->except('website'))
                    ->with('error', __('site.contact.throttled')));
        });
    }
}
