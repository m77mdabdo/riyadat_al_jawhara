<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\Post;
use App\Models\Project;
use App\Models\Service;
use App\Models\Setting;
use App\Models\StoneType;
use App\Support\FragmentCache;
use App\Support\LocalizedUrl;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class SitemapController extends Controller
{
    public function index(): Response
    {
        // Flushed on content changes; the one-hour TTL also picks up posts
        // whose publish date has passed.
        $xml = FragmentCache::remember('sitemap', fn () => $this->build(), 3600);

        return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    private function build(): string
    {
        $latest = fn (Builder $query): ?Carbon => ($max = $query->max('updated_at')) ? Carbon::parse($max) : null;
        $settingsUpdated = Setting::current()->updated_at;

        // Each page is listed once per language; every entry carries
        // hreflang alternates for all languages (+ x-default).
        $pages = [
            $this->page('home', [], '1.0', $latest(Project::active())),
            $this->page('services.index', [], '0.8', $latest(Service::active())),
            $this->page('stones.index', [], '0.8', $latest(StoneType::active())),
            $this->page('projects.index', [], '0.8', $latest(Project::active())),
            $this->page('blog.index', [], '0.7', $latest(Post::published())),
            $this->page('gallery', [], '0.6', $latest(Project::active())),
            $this->page('faq', [], '0.5', $latest(Faq::active())),
            $this->page('about', [], '0.6', $settingsUpdated),
            $this->page('contact', [], '0.6', $settingsUpdated),
            $this->page('legal.privacy', [], '0.2', $settingsUpdated),
            $this->page('legal.terms', [], '0.2', $settingsUpdated),
        ];

        foreach (Service::active()->get() as $service) {
            $pages[] = $this->page('services.show', ['service' => $service], '0.7', $service->updated_at);
        }
        foreach (StoneType::active()->get() as $stoneType) {
            $pages[] = $this->page('stones.show', ['stoneType' => $stoneType], '0.7', $stoneType->updated_at);
        }
        foreach (Project::active()->get() as $project) {
            $pages[] = $this->page('projects.show', ['project' => $project], '0.6', $project->updated_at);
        }
        foreach (Post::published()->get() as $post) {
            $pages[] = $this->page('blog.show', ['post' => $post], '0.5', $post->updated_at);
        }
        $pages = collect($pages);

        $locales = LocalizedUrl::supported();

        $urls = $pages->flatMap(function (array $page) use ($locales) {
            $alternates = collect($locales)->mapWithKeys(
                fn (string $locale) => [$locale => route($page['route'], ['locale' => $locale] + $page['params'])]
            );

            return collect($locales)->map(fn (string $locale) => [
                'loc' => $alternates[$locale],
                'alternates' => $alternates->put('x-default', $alternates[LocalizedUrl::default()]),
                'priority' => $page['priority'],
                'lastmod' => $page['lastmod'],
            ]);
        });

        return view('sitemap', ['urls' => $urls])->render();
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array{route: string, params: array<string, mixed>, priority: string, lastmod: ?CarbonInterface}
     */
    private function page(string $route, array $params, string $priority, ?CarbonInterface $lastmod): array
    {
        return ['route' => $route, 'params' => $params, 'priority' => $priority, 'lastmod' => $lastmod];
    }

    /**
     * robots.txt with an absolute Sitemap line built from the current host.
     * There must be no public/robots.txt: the web server would serve that
     * static file before Laravel sees the request.
     */
    public function robots(): Response
    {
        $body = implode("\n", [
            'User-agent: *',
            'Disallow: /admin',
            'Disallow: /lang/',
            '',
            'Sitemap: '.route('sitemap'),
            '',
        ]);

        return response($body, 200)->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
