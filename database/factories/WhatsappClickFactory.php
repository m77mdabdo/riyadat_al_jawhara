<?php

namespace Database\Factories;

use App\Models\WhatsappClick;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WhatsappClick>
 */
class WhatsappClickFactory extends Factory
{
    protected $model = WhatsappClick::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'page' => '/ar',
            'locale' => 'ar',
        ];
    }
}
