<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use Illuminate\Database\Seeder;

class BlogCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name_ar' => 'أخبار', 'name_en' => 'News', 'order' => 1],
            ['name_ar' => 'رؤى المشاريع', 'name_en' => 'Project Insights', 'order' => 2],
            ['name_ar' => 'نصائح العناية بالحجر', 'name_en' => 'Stone Care Tips', 'order' => 3],
        ];

        foreach ($categories as $category) {
            BlogCategory::updateOrCreate(['name_en' => $category['name_en']], $category);
        }
    }
}
