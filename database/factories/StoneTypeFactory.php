<?php

namespace Database\Factories;

use App\Models\StoneCategory;
use App\Models\StoneType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StoneType>
 */
class StoneTypeFactory extends Factory
{
    protected $model = StoneType::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'stone_category_id' => StoneCategory::factory(),
            'name_en' => ucfirst($this->faker->unique()->words(2, true)),
            'name_ar' => 'حجر '.$this->faker->unique()->numberBetween(1, 99999),
            'origin_en' => $this->faker->country(),
            'origin_ar' => 'إيطاليا',
            'description_en' => $this->faker->sentence(12),
            'description_ar' => 'وصف الحجر الطبيعي.',
            'is_active' => true,
            'order' => $this->faker->numberBetween(0, 20),
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
