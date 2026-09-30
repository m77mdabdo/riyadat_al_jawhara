<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThrottleIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_searching_does_not_use_up_the_comment_limit(): void
    {
        // Regression: unnamed throttle:N,1 middleware shared one counter per
        // IP across routes, so a few searches blocked posting a comment.
        $post = Post::factory()->create();

        for ($i = 0; $i < 6; $i++) {
            $this->get(route('search', ['q' => 'marble']))->assertOk();
        }

        $this->post(route('blog.comments.store', $post), ['name' => 'N', 'email' => 'n@example.test', 'body' => 'Hello'])
            ->assertRedirect()
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('comments', ['body' => 'Hello']);
    }

    public function test_browsing_search_does_not_block_the_catalogue_download(): void
    {
        for ($i = 0; $i < 21; $i++) {
            $this->get(route('search', ['q' => 'marble']))->assertOk();
        }

        $this->get(route('stones.catalogue'))->assertOk();
    }
}
