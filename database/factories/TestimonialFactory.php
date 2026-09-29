<?php

namespace Database\Factories;

use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Testimonial>
 */
class TestimonialFactory extends Factory
{
    protected $model = Testimonial::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_name' => $this->faker->name(),
            'rating' => $this->faker->numberBetween(4, 5),
            'comment_en' => $this->faker->sentence(14),
            'comment_ar' => 'خدمة ممتازة وتنفيذ دقيق.',
            'is_active' => true,
            'order' => $this->faker->numberBetween(0, 20),
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
