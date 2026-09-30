<?php

namespace App\Providers;

use App\Contracts\CatalogueGenerator;
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
use App\Services\Catalogue\MpdfCatalogueGenerator;
use App\Services\StockCandidateFinder;
use App\Services\StockMedia\PexelsProvider;
use App\Services\StockMedia\PixabayProvider;
use App\Services\StockMedia\UnsplashProvider;
use App\Services\StockMediaFetcher;
use App\Support\FragmentCache;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
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

        // Catalogue PDFs are fingerprinted by the stone data; also clear the
        // cached files whenever that data changes.
        // Settings (contact line, logo) and media are printed in the PDFs too.
        foreach ([StoneType::class, StoneCategory::class, StoneAttribute::class, Setting::class, Media::class] as $model) {
            $model::saved(fn () => app(CatalogueGenerator::class)->flush());
            $model::deleted(fn () => app(CatalogueGenerator::class)->flush());
        }

        // Rendered fragments (home sections, sitemap): any public content
        // change, settings save or media upload/removal invalidates them.
        foreach ([
            Service::class, StoneType::class, StoneCategory::class, StoneAttribute::class, Project::class,
            Post::class, BlogCategory::class, Tag::class, Faq::class, Slider::class, Testimonial::class,
            TeamMember::class, Setting::class, Media::class,
        ] as $model) {
            $model::saved(fn () => FragmentCache::flush());
            $model::deleted(fn () => FragmentCache::flush());
        }

        // One password policy for every place a password is set: profile page,
        // password reset, and app:create-admin.
        Password::defaults(fn () => Password::min(12)->letters()->numbers());

        // Public lead form: 5 submissions per minute per IP. A throttled visitor
        // is sent back to the form with their input intact and a translated
        // message instead of the bare 429 error page.
        // Click beacons (WhatsApp, analytics): generous, but not a free write endpoint.
        RateLimiter::for('beacons', fn (Request $request) => Limit::perMinute(20)->by($request->ip()));

        RateLimiter::for('contact', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->ip())
                ->response(fn () => back()
                    ->withInput($request->except('website'))
                    ->with('error', __('site.contact.throttled')));
        });
    }
}
