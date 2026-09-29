<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BlogCommentModerationTest extends TestCase
{
    use RefreshDatabase;

    private function publishedPost(): Post
    {
        return Post::create([
            'title_en' => 'Caring for marble',
            'title_ar' => 'العناية بالرخام',
            'body_en' => '<p>Body</p>',
            'body_ar' => '<p>المحتوى</p>',
            'is_published' => true,
            'published_at' => now()->subDay(),
        ]);
    }

    private function comment(Post $post, string $body, string $status, ?Comment $parent = null): Comment
    {
        return Comment::create([
            'post_id' => $post->id,
            'parent_id' => $parent?->id,
            'name' => 'Visitor',
            'email' => 'visitor@example.test',
            'body' => $body,
            'status' => $status,
        ]);
    }

    public function test_only_approved_replies_are_visible_at_every_nesting_level(): void
    {
        $post = $this->publishedPost();

        $root = $this->comment($post, 'ROOT_APPROVED', 'approved');
        $this->comment($post, 'ROOT_PENDING', 'pending');
        $this->comment($post, 'ROOT_SPAM', 'spam');

        $level1 = $this->comment($post, 'L1_APPROVED', 'approved', $root);
        $this->comment($post, 'L1_PENDING', 'pending', $root);
        $this->comment($post, 'L1_SPAM', 'spam', $root);

        $level2 = $this->comment($post, 'L2_APPROVED', 'approved', $level1);
        $this->comment($post, 'L2_SPAM', 'spam', $level1);

        // Deeper than the component's max indent (3) -- still must be filtered.
        $level3 = $this->comment($post, 'L3_APPROVED', 'approved', $level2);
        $this->comment($post, 'L4_APPROVED', 'approved', $level3);
        $this->comment($post, 'L4_PENDING', 'pending', $level3);

        // Approved reply under a spam parent: the parent is hidden, so it must be too.
        $spamParent = Comment::where('body', 'L1_SPAM')->first();
        $this->comment($post, 'ORPHANED_APPROVED', 'approved', $spamParent);

        $response = $this->get(route('blog.show', $post))->assertOk();

        foreach (['ROOT_APPROVED', 'L1_APPROVED', 'L2_APPROVED', 'L3_APPROVED', 'L4_APPROVED'] as $visible) {
            $response->assertSee($visible);
        }

        foreach (['ROOT_PENDING', 'ROOT_SPAM', 'L1_PENDING', 'L1_SPAM', 'L2_SPAM', 'L4_PENDING', 'ORPHANED_APPROVED'] as $hidden) {
            $response->assertDontSee($hidden);
        }
    }

    public function test_top_level_comment_count_is_unchanged(): void
    {
        $post = $this->publishedPost();
        $root = $this->comment($post, 'A', 'approved');
        $this->comment($post, 'B', 'approved');
        $this->comment($post, 'C', 'pending');
        $this->comment($post, 'D', 'approved', $root);

        // Heading counts approved top-level comments only, as before.
        $this->get(route('blog.show', $post))->assertOk()->assertSee('(2)');
    }

    public function test_comment_tree_is_loaded_without_n_plus_one(): void
    {
        $post = $this->publishedPost();
        $parent = $this->comment($post, 'ROOT', 'approved');
        for ($depth = 0; $depth < 6; $depth++) {
            $parent = $this->comment($post, "DEPTH_{$depth}", 'approved', $parent);
        }

        DB::enableQueryLog();
        $this->get(route('blog.show', $post))->assertOk()->assertSee('DEPTH_5');
        $commentQueries = collect(DB::getQueryLog())
            ->filter(fn (array $q) => str_contains($q['query'], 'from "comments"'))
            ->count();
        DB::disableQueryLog();

        $this->assertSame(1, $commentQueries);
    }
}
