<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\Post;
use App\Models\Project;
use App\Models\Service;
use App\Models\Setting;
use App\Models\StoneType;
use App\Support\LocalizedUrl;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $latest = fn (Builder $query): ?Carbon => ($max = $query->max('updated_at')) ? Carbon::parse($max) : null;
        $settingsUpdated = Setting::current()->updated_at;

        // Each page is listed once per language; every entry carries
        // hreflang alternates for all languages (+ x-default).
        $pages = collect([
            ['route' => 'home', 'params' => [], 'priority' => '1.0', 'lastmod' => $latest(Project::active())],
            ['route' => 'services.index', 'params' => [], 'priority' => '0.8', 'lastmod' => $latest(Service::active())],
            ['route' => 'stones.index', 'params' => [], 'priority' => '0.8', 'lastmod' => $latest(StoneType::active())],
            ['route' => 'projects.index', 'params' => [], 'priority' => '0.8', 'lastmod' => $latest(Project::active())],
            ['route' => 'blog.index', 'params' => [], 'priority' => '0.7', 'lastmod' => $latest(Post::published())],
            ['route' => 'gallery', 'params' => [], 'priority' => '0.6', 'lastmod' => $latest(Project::active())],
            ['route' => 'faq', 'params' => [], 'priority' => '0.5', 'lastmod' => $latest(Faq::active())],
            ['route' => 'about', 'params' => [], 'priority' => '0.6', 'lastmod' => $settingsUpdated],
            ['route' => 'contact', 'params' => [], 'priority' => '0.6', 'lastmod' => $settingsUpdated],
            ['route' => 'legal.privacy', 'params' => [], 'priority' => '0.2', 'lastmod' => $settingsUpdated],
            ['route' => 'legal.terms', 'params' => [], 'priority' => '0.2', 'lastmod' => $settingsUpdated],
        ]);

        Service::active()->get()->each(
            fn (Service $service) => $pages->push(['route' => 'services.show', 'params' => ['service' => $service], 'priority' => '0.7', 'lastmod' => $service->updated_at])
        );

        StoneType::active()->get()->each(
            fn (StoneType $stoneType) => $pages->push(['route' => 'stones.show', 'params' => ['stoneType' => $stoneType], 'priority' => '0.7', 'lastmod' => $stoneType->updated_at])
        );

        Project::active()->get()->each(
            fn (Project $project) => $pages->push(['route' => 'projects.show', 'params' => ['project' => $project], 'priority' => '0.6', 'lastmod' => $project->updated_at])
        );

        Post::published()->get()->each(
            fn (Post $post) => $pages->push(['route' => 'blog.show', 'params' => ['post' => $post], 'priority' => '0.5', 'lastmod' => $post->updated_at])
        );

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

        $xml = view('sitemap', ['urls' => $urls])->render();

        return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
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
