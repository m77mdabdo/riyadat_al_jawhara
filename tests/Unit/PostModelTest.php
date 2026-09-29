<?php

namespace Tests\Unit;

use App\Models\Concerns\HasActiveScope;
use App\Models\Post;
use PHPUnit\Framework\TestCase;

class PostModelTest extends TestCase
{
    public function test_post_does_not_use_the_active_scope_trait(): void
    {
        // posts has no is_active/order columns; Post::active() used to be
        // callable and would have thrown a SQL error.
        $this->assertNotContains(HasActiveScope::class, class_uses(Post::class));
    }
}
