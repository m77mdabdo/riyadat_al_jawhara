<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\Post;
use App\Models\Project;
use App\Models\Service;
use App\Models\StoneType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Site-wide search over the public content in both languages. Every query is
 * parameter-bound; LIKE wildcards typed by the visitor are escaped.
 */
class SearchController extends Controller
{
    private const PER_TYPE = 50;

    private const PER_PAGE = 12;

    public function index(Request $request): View
    {
        $term = trim(Str::limit((string) $request->query('q', ''), 100, ''));
        $results = collect();

        if (mb_strlen($term) >= 2) {
            $like = '%'.addcslashes($term, '%_\\').'%';
            $results = $this->search($like);
        }

        $page = LengthAwarePaginator::resolveCurrentPage();
        $paginator = new LengthAwarePaginator(
            $results->forPage($page, self::PER_PAGE)->values(),
            $results->count(),
            self::PER_PAGE,
            $page,
            ['path' => $request->url(), 'query' => ['q' => $term]],
        );

        return view('search.index', ['term' => $term, 'results' => $paginator]);
    }

    private function search(string $like): Collection
    {
        $match = function (Builder $query, array $columns) use ($like) {
            $query->where(function (Builder $q) use ($columns, $like) {
                foreach ($columns as $column) {
                    $q->orWhere($column, 'like', $like);
                }
            });
        };

        $bilingual = fn (string ...$fields) => collect($fields)->flatMap(fn ($f) => ["{$f}_ar", "{$f}_en"])->all();

        $services = Service::active()->ordered()->tap(fn ($q) => $match($q, $bilingual('name', 'description')))
            ->limit(self::PER_TYPE)->get()
            ->map(fn (Service $s) => $this->item('service', $s->name, $s->description, route('services.show', $s)));

        $stones = StoneType::active()->ordered()->tap(fn ($q) => $match($q, $bilingual('name', 'origin', 'description')))
            ->limit(self::PER_TYPE)->get()
            ->map(fn (StoneType $s) => $this->item('stone', $s->name, $s->description, route('stones.show', $s)));

        $projects = Project::active()->ordered()->tap(fn ($q) => $match($q, $bilingual('title', 'description', 'location', 'city')))
            ->limit(self::PER_TYPE)->get()
            ->map(fn (Project $p) => $this->item('project', $p->title, $p->description, route('projects.show', $p)));

        $posts = Post::published()->latest('published_at')->tap(fn ($q) => $match($q, $bilingual('title', 'excerpt', 'body')))
            ->limit(self::PER_TYPE)->get()
            ->map(fn (Post $p) => $this->item('post', $p->title, $p->excerpt ?: $p->body, route('blog.show', $p)));

        $faqs = Faq::active()->ordered()->tap(fn ($q) => $match($q, $bilingual('question', 'answer')))
            ->limit(self::PER_TYPE)->get()
            ->map(fn (Faq $f) => $this->item('faq', $f->question, $f->answer, route('faq').'#faq-'.$f->id));

        return $services->concat($stones)->concat($projects)->concat($posts)->concat($faqs)->values();
    }

    /**
     * @return array{type: string, title: string, excerpt: string, url: string}
     */
    private function item(string $type, ?string $title, ?string $text, string $url): array
    {
        return [
            'type' => $type,
            'title' => (string) $title,
            'excerpt' => Str::limit(trim(preg_replace('/\s+/u', ' ', strip_tags((string) $text))), 180),
            'url' => $url,
        ];
    }
}
