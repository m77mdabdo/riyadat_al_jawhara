<?php

namespace Database\Factories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    protected $model = Post::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title_en' => ucfirst($this->faker->unique()->words(5, true)),
            'title_ar' => 'مقال '.$this->faker->unique()->numberBetween(1, 99999),
            'excerpt_en' => $this->faker->sentence(15),
            'excerpt_ar' => 'ملخص المقال.',
            'body_en' => '<p>'.$this->faker->paragraph().'</p>',
            'body_ar' => '<p>محتوى المقال.</p>',
            'is_published' => true,
            'published_at' => $this->faker->dateTimeBetween('-1 year', '-1 day'),
        ];
    }

    public function draft(): static
    {
        return $this->state(['is_published' => false]);
    }

    public function scheduled(): static
    {
        return $this->state(['published_at' => now()->addWeek()]);
    }
}
