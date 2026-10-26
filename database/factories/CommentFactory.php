<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comment>
 */
class CommentFactory extends Factory
{
    protected $model = Comment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'post_id' => Post::factory(),
            'name' => $this->faker->name(),
            'email' => $this->faker->safeEmail(),
            'body' => $this->faker->sentence(12),
            'status' => 'approved',
        ];
    }

    public function pending(): static
    {
        return $this->state(['status' => 'pending']);
    }

    public function spam(): static
    {
        return $this->state(['status' => 'spam']);
    }

    public function replyTo(Comment $parent): static
    {
        return $this->state(['post_id' => $parent->post_id, 'parent_id' => $parent->id]);
    }
}
