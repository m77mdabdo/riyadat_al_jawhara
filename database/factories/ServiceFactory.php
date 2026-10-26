<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    protected $model = Service::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name_en' => ucfirst($this->faker->unique()->words(2, true)),
            'name_ar' => 'خدمة '.$this->faker->unique()->numberBetween(1, 99999),
            'icon' => 'heroicon-o-cube',
            'description_en' => $this->faker->sentence(12),
            'description_ar' => 'وصف الخدمة.',
            'is_active' => true,
            'order' => $this->faker->numberBetween(0, 20),
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
