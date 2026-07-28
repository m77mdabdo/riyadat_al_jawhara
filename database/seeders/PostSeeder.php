<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::where('email', 'admin@jra.sa')->first();

        $posts = [
            [
                'title_ar' => 'كيف تختار نوع الرخام المناسب لمنزلك',
                'title_en' => 'How to Choose the Right Marble for Your Home',
                'excerpt_ar' => 'دليل مختصر لمساعدتك على اختيار الرخام المناسب حسب الاستخدام والميزانية.',
                'excerpt_en' => 'A short guide to help you choose the right marble based on usage and budget.',
                'body_ar' => 'يعتمد اختيار الرخام المناسب على عدة عوامل منها مكان الاستخدام، مستوى الحركة، والميزانية المتاحة. في هذا المقال نستعرض أهم النصائح لاختيار الرخام الأمثل لمشروعك.',
                'body_en' => 'Choosing the right marble depends on several factors including the area of use, foot traffic level, and available budget. In this article, we cover the top tips for selecting the ideal marble for your project.',
                'is_published' => true,
                'published_at' => now()->subDays(20),
            ],
            [
                'title_ar' => 'اتجاهات تصميم الواجهات الحجرية لعام 2026',
                'title_en' => 'Stone Facade Design Trends for 2026',
                'excerpt_ar' => 'أبرز اتجاهات تصميم الواجهات الحجرية التي تتصدر المشهد المعماري هذا العام.',
                'excerpt_en' => 'The top stone facade design trends leading the architectural scene this year.',
                'body_ar' => 'شهد عام 2026 تحولًا ملحوظًا نحو الواجهات الحجرية التي تجمع بين الطابع التراثي والحداثة، مع التركيز على الأحجار المحلية مثل الحجر الهاشمي.',
                'body_en' => '2026 has seen a notable shift toward stone facades that blend heritage character with modernity, with a focus on local stones such as Hashemi stone.',
                'is_published' => true,
                'published_at' => now()->subDays(5),
            ],
        ];

        foreach ($posts as $post) {
            Post::updateOrCreate(
                ['title_en' => $post['title_en']],
                $post + ['author_id' => $author?->id]
            );
        }
    }
}
