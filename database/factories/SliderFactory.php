<?php

namespace Database\Factories;

use App\Models\Slider;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Slider>
 */
class SliderFactory extends Factory
{
    protected $model = Slider::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title_en' => ucfirst($this->faker->words(4, true)),
            'title_ar' => 'عنوان الشريحة',
            'subtitle_en' => $this->faker->sentence(8),
            'subtitle_ar' => 'نص فرعي',
            'is_active' => true,
            'order' => $this->faker->numberBetween(0, 20),
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
