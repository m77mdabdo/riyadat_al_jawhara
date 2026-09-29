<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Models\BlogCategory;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(Request $request): View
    {
        $query = Post::published()->with(['blogCategory', 'tags']);

        if ($request->filled('category')) {
            $category = $request->string('category')->toString();
            $query->whereHas('blogCategory', fn ($q) => $q->where('slug', $category));
        }

        if ($request->filled('tag')) {
            $tag = $request->string('tag')->toString();
            $query->whereHas('tags', fn ($q) => $q->where('slug', $tag));
        }

        if ($request->filled('q')) {
            $search = $request->string('q')->toString();
            $query->where(function ($q) use ($search) {
                $q->where('title_en', 'like', "%{$search}%")
                    ->orWhere('title_ar', 'like', "%{$search}%")
                    ->orWhere('excerpt_en', 'like', "%{$search}%")
                    ->orWhere('excerpt_ar', 'like', "%{$search}%");
            });
        }

        return view('blog.index', [
            'posts' => $query->latest('published_at')->paginate(9)->withQueryString(),
            'categories' => BlogCategory::ordered()->get(),
            'tags' => Tag::orderBy('name_en')->get(),
            'activeCategory' => $request->string('category')->toString(),
            'activeTag' => $request->string('tag')->toString(),
            'search' => $request->string('q')->toString(),
        ]);
    }

    public function show(Request $request, Post $post): View
    {
        abort_unless($post->is_published && $post->published_at?->lessThanOrEqualTo(now()), 404);

        $sessionKey = 'viewed_post_'.$post->id;

        if (! $request->session()->has($sessionKey)) {
            $post->increment('views_count');
            $request->session()->put($sessionKey, true);
        }

        $post->load(['blogCategory', 'tags', 'author']);
        $this->loadApprovedCommentTree($post);

        $related = Post::published()
            ->where('id', '!=', $post->id)
            ->when($post->blog_category_id, fn ($q) => $q->where('blog_category_id', $post->blog_category_id))
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('blog.show', [
            'post' => $post,
            'related' => $related,
        ]);
    }

    /**
     * Loads every approved comment for the post in one query and wires up
     * `approvedComments` (top level) and `approvedReplies` (every nested
     * level) in memory. This covers any nesting depth without N+1 queries.
     * A reply whose parent is not approved is never reached, so it stays
     * hidden too.
     */
    private function loadApprovedCommentTree(Post $post): void
    {
        $byParent = $post->comments()
            ->approved()
            ->orderBy('id')
            ->get()
            ->groupBy(fn (Comment $comment) => $comment->parent_id ?? 0);

        $byParent->flatten()->each(
            fn (Comment $comment) => $comment->setRelation('approvedReplies', $byParent->get($comment->id, collect())->values())
        );

        $post->setRelation('approvedComments', $byParent->get(0, collect())->values());
    }

    public function storeComment(StoreCommentRequest $request, Post $post): RedirectResponse
    {
        abort_unless($post->is_published, 404);

        $data = $request->validated();

        // Honeypot: bots that fill this field are silently marked as spam,
        // not rejected, so they don't learn the submission was caught.
        $isSpam = filled($data['website'] ?? null);

        Comment::create([
            'post_id' => $post->id,
            'parent_id' => $data['parent_id'] ?? null,
            'name' => $data['name'],
            'email' => $data['email'],
            'body' => $data['body'],
            'status' => $isSpam ? 'spam' : 'pending',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()
            ->with('success', __('site.blog.comment_pending_notice'))
            ->withFragment('comments');
    }
}
