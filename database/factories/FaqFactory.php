<?php

namespace Database\Factories;

use App\Models\Faq;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Faq>
 */
class FaqFactory extends Factory
{
    protected $model = Faq::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'question_en' => rtrim($this->faker->sentence(8), '.').'?',
            'question_ar' => 'سؤال شائع؟',
            'answer_en' => $this->faker->paragraph(),
            'answer_ar' => 'إجابة السؤال.',
            'is_active' => true,
            'order' => $this->faker->numberBetween(0, 20),
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
