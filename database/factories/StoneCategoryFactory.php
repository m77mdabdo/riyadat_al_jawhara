<?php

namespace Database\Factories;

use App\Models\StoneCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StoneCategory>
 */
class StoneCategoryFactory extends Factory
{
    protected $model = StoneCategory::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name_en' => ucfirst($this->faker->unique()->word()).' Stone',
            'name_ar' => 'فئة '.$this->faker->unique()->numberBetween(1, 99999),
            'order' => $this->faker->numberBetween(0, 20),
        ];
    }
}
