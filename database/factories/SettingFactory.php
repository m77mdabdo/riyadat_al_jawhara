<?php

namespace Database\Factories;

use App\Models\Setting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Setting>
 */
class SettingFactory extends Factory
{
    protected $model = Setting::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'phone' => '+966 11 000 0000',
            'whatsapp' => '966500000000',
            'email' => 'info@example.test',
            'address_en' => 'Example Street, Riyadh',
            'address_ar' => 'شارع المثال، الرياض',
        ];
    }
}
