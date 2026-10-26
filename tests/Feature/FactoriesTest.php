<?php

namespace Tests\Feature;

use App\Models\BlogCategory;
use App\Models\Comment;
use App\Models\Faq;
use App\Models\Lead;
use App\Models\LeadNote;
use App\Models\Post;
use App\Models\Project;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Slider;
use App\Models\StoneAttribute;
use App\Models\StoneCategory;
use App\Models\StoneType;
use App\Models\Tag;
use App\Models\TeamMember;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class FactoriesTest extends TestCase
{
    use RefreshDatabase;

    public static function models(): array
    {
        return array_map(fn ($class) => [$class], [
            BlogCategory::class, Comment::class, Faq::class, Lead::class, LeadNote::class, Post::class,
            Project::class, Service::class, Setting::class, Slider::class, StoneAttribute::class,
            StoneCategory::class, StoneType::class, Tag::class, TeamMember::class, Testimonial::class, User::class,
        ]);
    }

    #[DataProvider('models')]
    public function test_every_model_has_a_working_factory(string $class): void
    {
        $model = $class::factory()->create();

        $this->assertTrue($model->exists);
        $this->assertModelExists($model);
    }

    public function test_factory_states(): void
    {
        $this->assertFalse(Project::factory()->inactive()->create()->is_active);
        $this->assertTrue(Project::factory()->featured()->create()->is_featured);
        $this->assertSame(0, Post::published()->whereKey(Post::factory()->draft()->create()->id)->count());
        $this->assertSame(0, Post::published()->whereKey(Post::factory()->scheduled()->create()->id)->count());

        $parent = Comment::factory()->create();
        $reply = Comment::factory()->replyTo($parent)->spam()->create();
        $this->assertTrue($reply->parent->is($parent));
        $this->assertSame('spam', $reply->status);

        $this->assertSame(StoneAttribute::COLOUR, StoneAttribute::factory()->ofType(StoneAttribute::COLOUR)->create()->type);
    }
}
