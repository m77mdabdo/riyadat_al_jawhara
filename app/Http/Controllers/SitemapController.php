<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\Post;
use App\Models\Project;
use App\Models\Service;
use App\Models\Setting;
use App\Models\StoneType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $latest = fn (Builder $query): ?Carbon => ($max = $query->max('updated_at')) ? Carbon::parse($max) : null;
        $settingsUpdated = Setting::current()->updated_at;

        $urls = collect([
            ['loc' => route('home'), 'priority' => '1.0', 'lastmod' => $latest(Project::active())],
            ['loc' => route('services.index'), 'priority' => '0.8', 'lastmod' => $latest(Service::active())],
            ['loc' => route('stones.index'), 'priority' => '0.8', 'lastmod' => $latest(StoneType::active())],
            ['loc' => route('projects.index'), 'priority' => '0.8', 'lastmod' => $latest(Project::active())],
            ['loc' => route('blog.index'), 'priority' => '0.7', 'lastmod' => $latest(Post::published())],
            ['loc' => route('faq'), 'priority' => '0.5', 'lastmod' => $latest(Faq::active())],
            ['loc' => route('about'), 'priority' => '0.6', 'lastmod' => $settingsUpdated],
            ['loc' => route('contact'), 'priority' => '0.6', 'lastmod' => $settingsUpdated],
            ['loc' => route('legal.privacy'), 'priority' => '0.2', 'lastmod' => $settingsUpdated],
            ['loc' => route('legal.terms'), 'priority' => '0.2', 'lastmod' => $settingsUpdated],
        ]);

        Service::active()->get()->each(
            fn (Service $service) => $urls->push(['loc' => route('services.show', $service), 'priority' => '0.7', 'lastmod' => $service->updated_at])
        );

        StoneType::active()->get()->each(
            fn (StoneType $stoneType) => $urls->push(['loc' => route('stones.show', $stoneType), 'priority' => '0.7', 'lastmod' => $stoneType->updated_at])
        );

        Project::active()->get()->each(
            fn (Project $project) => $urls->push(['loc' => route('projects.show', $project), 'priority' => '0.6', 'lastmod' => $project->updated_at])
        );

        Post::published()->get()->each(
            fn (Post $post) => $urls->push(['loc' => route('blog.show', $post), 'priority' => '0.5', 'lastmod' => $post->updated_at])
        );

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
