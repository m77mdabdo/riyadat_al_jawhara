<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * The only seeder that may run against production. It seeds the data the
 * application needs to function and nothing else -- no users, no demo
 * content. Safe to run repeatedly.
 *
 *   php artisan db:seed --class=ProductionSeeder --force
 */
class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
        ]);
    }
}
