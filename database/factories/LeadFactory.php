<?php

namespace Database\Factories;

use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lead>
 */
class LeadFactory extends Factory
{
    protected $model = Lead::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'phone' => '+9665'.$this->faker->numerify('########'),
            'email' => $this->faker->safeEmail(),
            'city' => $this->faker->city(),
            'message' => $this->faker->sentence(15),
            'status' => 'new',
            'source' => 'website',
            'locale' => 'en',
        ];
    }
}
