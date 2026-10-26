<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title_en' => ucfirst($this->faker->unique()->words(3, true)),
            'title_ar' => 'مشروع '.$this->faker->unique()->numberBetween(1, 99999),
            'description_en' => $this->faker->paragraph(),
            'description_ar' => 'وصف المشروع.',
            'location_en' => $this->faker->city(),
            'location_ar' => 'الرياض',
            'city_en' => 'Riyadh',
            'city_ar' => 'الرياض',
            'sector' => $this->faker->randomElement(Project::SECTORS),
            'area_m2' => $this->faker->numberBetween(50, 5000),
            'completed_at' => $this->faker->dateTimeBetween('-3 years'),
            'is_featured' => false,
            'is_active' => true,
            'order' => $this->faker->numberBetween(0, 20),
        ];
    }

    public function featured(): static
    {
        return $this->state(['is_featured' => true]);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
