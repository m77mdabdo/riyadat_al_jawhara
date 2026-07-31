<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Project;
use App\Models\Service;
use App\Models\StoneType;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $urls = collect([
            ['loc' => route('home'), 'priority' => '1.0'],
            ['loc' => route('services.index'), 'priority' => '0.8'],
            ['loc' => route('stones.index'), 'priority' => '0.8'],
            ['loc' => route('projects.index'), 'priority' => '0.8'],
            ['loc' => route('blog.index'), 'priority' => '0.7'],
            ['loc' => route('about'), 'priority' => '0.6'],
            ['loc' => route('contact'), 'priority' => '0.6'],
        ]);

        Service::active()->get()->each(
            fn (Service $service) => $urls->push(['loc' => route('services.show', $service), 'priority' => '0.7'])
        );

        StoneType::active()->get()->each(
            fn (StoneType $stoneType) => $urls->push(['loc' => route('stones.show', $stoneType), 'priority' => '0.7'])
        );

        Project::active()->get()->each(
            fn (Project $project) => $urls->push(['loc' => route('projects.show', $project), 'priority' => '0.6'])
        );

        Post::published()->get()->each(
            fn (Post $post) => $urls->push(['loc' => route('blog.show', $post), 'priority' => '0.5'])
        );

        $xml = view('sitemap', ['urls' => $urls])->render();

        return response($xml, 200)->header('Content-Type', 'text/xml');
    }
}
