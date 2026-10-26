<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * A plain `php artisan db:seed` only seeds what production needs (roles).
     * Demo content is opt-in so it can never reach production by accident:
     *
     *   php artisan db:seed --class=DemoSeeder
     */
    public function run(): void
    {
        $this->call([
            ProductionSeeder::class,
        ]);
    }
}
