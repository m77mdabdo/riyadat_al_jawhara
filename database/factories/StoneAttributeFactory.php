<?php

namespace Database\Factories;

use App\Models\StoneAttribute;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StoneAttribute>
 */
class StoneAttributeFactory extends Factory
{
    protected $model = StoneAttribute::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => $this->faker->randomElement(array_keys(StoneAttribute::TYPES)),
            'name_en' => ucfirst($this->faker->unique()->word()),
            'name_ar' => 'قيمة '.$this->faker->unique()->numberBetween(1, 99999),
            'order' => $this->faker->numberBetween(0, 20),
            'is_active' => true,
        ];
    }

    public function ofType(string $type): static
    {
        return $this->state(['type' => $type]);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
