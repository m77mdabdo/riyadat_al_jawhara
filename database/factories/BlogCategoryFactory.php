<?php

namespace Database\Factories;

use App\Models\BlogCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BlogCategory>
 */
class BlogCategoryFactory extends Factory
{
    protected $model = BlogCategory::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name_en' => ucfirst($this->faker->unique()->words(2, true)),
            'name_ar' => 'تصنيف '.$this->faker->unique()->numberBetween(1, 99999),
            'order' => $this->faker->numberBetween(0, 20),
        ];
    }
}
