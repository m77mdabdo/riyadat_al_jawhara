<?php

namespace Tests\Unit;

use App\Support\Like;
use PHPUnit\Framework\TestCase;

class LikeTest extends TestCase
{
    public function test_wildcards_and_the_escape_character_are_taken_literally(): void
    {
        $this->assertSame('%50!% off%', Like::contains('50% off'));
        $this->assertSame('%a!_b%', Like::contains('a_b'));
        $this->assertSame('%wow!!%', Like::contains('wow!'));
        $this->assertSame('%رخام%', Like::contains('رخام'));
    }
}
