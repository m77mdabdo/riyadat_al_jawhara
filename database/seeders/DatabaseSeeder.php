<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            SettingSeeder::class,
            SliderSeeder::class,
            ServiceSeeder::class,
            StoneCategorySeeder::class,
            ProjectSeeder::class,
            TestimonialSeeder::class,
            TeamMemberSeeder::class,
            FaqSeeder::class,
            LeadSeeder::class,
            PostSeeder::class,
        ]);
    }
}
