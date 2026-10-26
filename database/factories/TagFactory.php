<?php

namespace Database\Factories;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tag>
 */
class TagFactory extends Factory
{
    protected $model = Tag::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name_en' => ucfirst($this->faker->unique()->word()),
            'name_ar' => 'وسم '.$this->faker->unique()->numberBetween(1, 99999),
        ];
    }
}
