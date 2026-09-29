<?php

namespace Database\Factories;

use App\Models\TeamMember;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeamMember>
 */
class TeamMemberFactory extends Factory
{
    protected $model = TeamMember::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name_en' => $this->faker->name(),
            'name_ar' => 'عضو الفريق',
            'position_en' => $this->faker->jobTitle(),
            'position_ar' => 'مهندس',
            'is_active' => true,
            'order' => $this->faker->numberBetween(0, 20),
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
