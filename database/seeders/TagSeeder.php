<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            ['name_ar' => 'رخام', 'name_en' => 'Marble'],
            ['name_ar' => 'جرانيت', 'name_en' => 'Granite'],
            ['name_ar' => 'نصائح الصيانة', 'name_en' => 'Maintenance Tips'],
            ['name_ar' => 'أبرز المشاريع', 'name_en' => 'Project Spotlight'],
            ['name_ar' => 'اتجاهات التصميم', 'name_en' => 'Trends'],
            ['name_ar' => 'حجر طبيعي', 'name_en' => 'Natural Stone'],
            ['name_ar' => 'واجهات معمارية', 'name_en' => 'Facades'],
            ['name_ar' => 'ديكور داخلي', 'name_en' => 'Interior Decor'],
        ];

        foreach ($tags as $tag) {
            Tag::updateOrCreate(['name_en' => $tag['name_en']], $tag);
        }
    }
}
