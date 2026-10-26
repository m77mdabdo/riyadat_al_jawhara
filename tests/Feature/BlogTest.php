<?php

namespace Tests\Feature;

use App\Models\BlogCategory;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogTest extends TestCase
{
    use RefreshDatabase;

    private function comment(Post $post, array $overrides = []): array
    {
        return array_merge(['name' => 'Reader', 'email' => 'reader@example.test', 'body' => 'Useful article, thanks.'], $overrides);
    }

    public function test_index_lists_only_published_posts(): void
    {
        Post::factory()->create(['title_en' => 'Live Post']);
        Post::factory()->draft()->create(['title_en' => 'Draft Post']);
        Post::factory()->scheduled()->create(['title_en' => 'Future Post']);

        $this->get(route('blog.index'))->assertOk()->assertSee('Live Post')->assertDontSee('Draft Post')->assertDontSee('Future Post');
    }

    public function test_index_filters_by_category_tag_and_search(): void
    {
        $category = BlogCategory::factory()->create(['name_en' => 'Care']);
        $tag = Tag::factory()->create(['name_en' => 'Granite']);
        $inCategory = Post::factory()->create(['title_en' => 'Cleaning marble', 'blog_category_id' => $category->id]);
        $tagged = Post::factory()->create(['title_en' => 'Granite countertops']);
        $tagged->tags()->attach($tag);
        Post::factory()->create(['title_en' => 'Unrelated news', 'excerpt_en' => 'nothing']);

        $titles = fn (array $q) => $this->get(route('blog.index', $q))->viewData('posts')->pluck('title_en')->all();

        $this->assertSame(['Cleaning marble'], $titles(['category' => $category->slug]));
        $this->assertSame(['Granite countertops'], $titles(['tag' => $tag->slug]));
        $this->assertSame(['Cleaning marble'], $titles(['q' => 'cleaning']));
        $this->assertSame([], $titles(['category' => 'nope']));
    }

    public function test_unpublished_and_scheduled_posts_are_404(): void
    {
        $this->get(route('blog.show', Post::factory()->draft()->create()))->assertNotFound();
        $this->get(route('blog.show', Post::factory()->scheduled()->create()))->assertNotFound();
    }

    public function test_views_are_counted_once_per_session(): void
    {
        $post = Post::factory()->create(['views_count' => 0]);

        $this->get(route('blog.show', $post));
        $this->get(route('blog.show', $post));

        $this->assertSame(1, $post->fresh()->views_count);
    }

    public function test_comment_is_stored_as_pending_and_not_shown(): void
    {
        $post = Post::factory()->create();

        $this->from(route('blog.show', $post))
            ->post(route('blog.comments.store', $post), $this->comment($post, ['body' => 'PENDING_BODY']))
            ->assertRedirect()
            ->assertSessionHas('success', __('site.blog.comment_pending_notice'));

        $this->assertDatabaseHas('comments', ['post_id' => $post->id, 'status' => 'pending', 'body' => 'PENDING_BODY']);
        $this->get(route('blog.show', $post))->assertDontSee('PENDING_BODY');
    }

    public function test_honeypot_comment_is_stored_as_spam(): void
    {
        $post = Post::factory()->create();

        $this->post(route('blog.comments.store', $post), $this->comment($post, ['website' => 'http://spam.test']))
            ->assertSessionHas('success');

        $this->assertSame('spam', Comment::firstOrFail()->status);
    }

    public function test_reply_parent_must_belong_to_the_same_post(): void
    {
        $post = Post::factory()->create();
        $foreign = Comment::factory()->create();

        $this->post(route('blog.comments.store', $post), $this->comment($post, ['parent_id' => $foreign->id]))
            ->assertSessionHasErrors('parent_id');
    }

    public function test_comments_are_rate_limited(): void
    {
        $post = Post::factory()->create();

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('blog.comments.store', $post), $this->comment($post))->assertRedirect();
        }

        $this->post(route('blog.comments.store', $post), $this->comment($post))->assertStatus(429);
        $this->assertSame(5, Comment::count());
    }

    public function test_comment_validation(): void
    {
        $post = Post::factory()->create();

        $this->post(route('blog.comments.store', $post), ['name' => '', 'email' => 'not-email', 'body' => str_repeat('x', 2001)])
            ->assertSessionHasErrors(['name', 'email', 'body']);
    }
}
