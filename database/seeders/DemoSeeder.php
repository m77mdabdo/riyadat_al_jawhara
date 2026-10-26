<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Fills a local/staging database with FAKE bilingual demo content: services,
 * stones, projects, testimonials, team members, FAQs, leads, blog posts and
 * comments, and placeholder contact settings. Never run this in production --
 * every name, phone number, review and address it creates is invented.
 *
 *   php artisan db:seed --class=DemoSeeder
 *
 * It creates no user accounts; use `php artisan app:create-admin` for that.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ProductionSeeder::class,
            SettingSeeder::class,
            SliderSeeder::class,
            ServiceSeeder::class,
            StoneCategorySeeder::class,
            StoneAttributeSeeder::class,
            ProjectSeeder::class,
            TestimonialSeeder::class,
            TeamMemberSeeder::class,
            FaqSeeder::class,
            LeadSeeder::class,
            BlogCategorySeeder::class,
            TagSeeder::class,
            PostSeeder::class,
            CommentSeeder::class,
        ]);
    }
}
