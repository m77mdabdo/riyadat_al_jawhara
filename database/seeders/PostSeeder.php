<?php

namespace Database\Seeders;

// STOCK PHOTO — post cover images below are sourced live from Pexels/Pixabay
// via App\Services\StockMediaFetcher (free for commercial use, no
// attribution required per their license terms as of 2026-07). Replace with
// real JRA photography/graphics before final handoff.

use App\Models\BlogCategory;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use App\Services\StockMediaFetcher;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    public function run(StockMediaFetcher $fetcher): void
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
                'category' => 'Stone Care Tips',
                'tags' => ['Marble', 'Maintenance Tips'],
                'search' => 'marble texture close up',
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
                'category' => 'Project Insights',
                'tags' => ['Facades', 'Trends'],
                'search' => 'modern building facade exterior',
            ],
            [
                'title_ar' => '٥ علامات تدل على حاجة أرضية الرخام لعناية احترافية',
                'title_en' => '5 Signs Your Marble Floor Needs Professional Care',
                'excerpt_ar' => 'تعرف على العلامات المبكرة التي تشير إلى حاجة أرضيتك الرخامية لصيانة متخصصة.',
                'excerpt_en' => 'Learn the early warning signs that your marble floor needs specialist maintenance.',
                'body_ar' => 'من الخدوش السطحية إلى فقدان اللمعان، هناك عدة علامات تدل على أن أرضية الرخام لديك بحاجة إلى عناية احترافية قبل أن تتفاقم المشكلة. في هذا المقال نستعرض هذه العلامات وكيفية التعامل معها.',
                'body_en' => 'From surface scratches to a loss of shine, there are several signs that your marble floor needs professional attention before the problem worsens. In this article, we walk through those signs and how to address them.',
                'is_published' => true,
                'published_at' => now()->subDays(14),
                'category' => 'Stone Care Tips',
                'tags' => ['Marble', 'Maintenance Tips'],
                'search' => 'marble floor cleaning',
            ],
            [
                'title_ar' => 'الجرانيت أم الكوارتز: ما الأنسب لمطبخك؟',
                'title_en' => 'Granite vs Quartz: Which Is Right for Your Kitchen?',
                'excerpt_ar' => 'مقارنة عملية بين الجرانيت والكوارتز لمساعدتك على اتخاذ القرار الأنسب لمطبخك.',
                'excerpt_en' => 'A practical comparison between granite and quartz to help you decide what fits your kitchen best.',
                'body_ar' => 'كل من الجرانيت والكوارتز يقدم مزايا مختلفة من حيث المتانة والمظهر وسهولة الصيانة. في هذا المقال نقارن بينهما بالتفصيل لمساعدتك في اختيار السطح الأنسب لمطبخك.',
                'body_en' => 'Granite and quartz each offer different advantages in durability, appearance, and ease of maintenance. In this article, we compare them in detail to help you choose the right countertop surface for your kitchen.',
                'is_published' => true,
                'published_at' => now()->subDays(10),
                'category' => 'Stone Care Tips',
                'tags' => ['Granite', 'Trends'],
                'search' => 'granite kitchen countertop',
            ],
            [
                'title_ar' => 'نظرة داخل مشروع تجديد فيلا فاخرة في الرياض',
                'title_en' => 'Inside a Luxury Villa Renovation in Riyadh',
                'excerpt_ar' => 'جولة داخل أحد أبرز مشاريع تجديد الفلل الفاخرة التي نفذها فريقنا في الرياض.',
                'excerpt_en' => 'A behind-the-scenes look at one of our standout luxury villa renovation projects in Riyadh.',
                'body_ar' => 'تطلب هذا المشروع دقة عالية في اختيار وتركيب الرخام لتحقيق رؤية العميل بأسلوب عصري وفاخر في آنٍ واحد. نستعرض في هذا المقال أبرز تفاصيل التنفيذ.',
                'body_en' => 'This project called for careful stone selection and installation to bring the client\'s vision to life in a way that felt both modern and luxurious. In this article, we walk through the key details of the execution.',
                'is_published' => true,
                'published_at' => now()->subDays(3),
                'category' => 'Project Insights',
                'tags' => ['Project Spotlight', 'Interior Decor'],
                'search' => 'luxury villa interior',
            ],
            [
                'title_ar' => 'الجاذبية الخالدة لواجهات الحجر الطبيعي',
                'title_en' => 'The Timeless Appeal of Natural Stone Facades',
                'excerpt_ar' => 'لماذا لا تزال واجهات الحجر الطبيعي خيارًا مفضلاً في العمارة الحديثة والتقليدية.',
                'excerpt_en' => 'Why natural stone facades remain a favorite choice across both modern and traditional architecture.',
                'body_ar' => 'يجمع الحجر الطبيعي بين المتانة الاستثنائية والجمال الذي لا يتأثر بمرور الزمن، ما يجعله خيارًا مستمرًا لدى المهندسين المعماريين وأصحاب المشاريع على حد سواء.',
                'body_en' => 'Natural stone combines exceptional durability with a beauty that doesn\'t fade over time, making it a lasting favorite among architects and project owners alike.',
                'is_published' => true,
                'published_at' => now()->subDays(30),
                'category' => 'News',
                'tags' => ['Natural Stone', 'Facades'],
                'search' => 'stone facade building',
            ],
            [
                'title_ar' => 'رياضة الجوهرة الإبداعية تُنجز مشروع برج تجاري كبير',
                'title_en' => 'JRA Completes Major Commercial Tower Project',
                'excerpt_ar' => 'فريقنا يُتم بنجاح تركيب الرخام الطبيعي لواجهة أحد أبراج الأعمال البارزة.',
                'excerpt_en' => 'Our team has successfully completed natural stone installation for the facade of a prominent business tower.',
                'body_ar' => 'يمثل هذا الإنجاز محطة مهمة في مسيرة الشركة، حيث تم تنفيذ المشروع بالكامل ضمن الجدول الزمني المحدد وبأعلى معايير الجودة.',
                'body_en' => 'This milestone marks an important step in our company\'s journey, with the entire project delivered on schedule and to the highest quality standards.',
                'is_published' => true,
                'published_at' => now()->subDays(7),
                'category' => 'News',
                'tags' => ['Project Spotlight'],
                'search' => 'construction project team',
            ],
            [
                'title_ar' => 'اتجاهات التصميم الداخلي لعام 2026: الحجر في الصدارة',
                'title_en' => '2026 Interior Design Trends: Stone Takes Center Stage',
                'excerpt_ar' => 'كيف أصبح الحجر الطبيعي والرخام عنصرًا أساسيًا في تصاميم الديكور الداخلي هذا العام.',
                'excerpt_en' => 'How natural stone and marble have become a defining element of interior decor this year.',
                'body_ar' => 'من المطابخ إلى الحمامات وصولاً إلى ردهات الاستقبال، يشهد التصميم الداخلي عودة قوية لاستخدام الحجر الطبيعي كعنصر جمالي ووظيفي في آنٍ واحد.',
                'body_en' => 'From kitchens to bathrooms to reception lobbies, interior design is seeing a strong resurgence in the use of natural stone as both an aesthetic and functional element.',
                'is_published' => true,
                'published_at' => now()->subDays(1),
                'category' => 'News',
                'tags' => ['Trends', 'Interior Decor'],
                'search' => 'interior design blog',
            ],
            [
                'title_ar' => 'قريبًا: صالة عرض الأحجار الجديدة التابعة لنا',
                'title_en' => 'Coming Soon: Our New Stone Gallery Showroom',
                'excerpt_ar' => 'نستعد لافتتاح صالة عرض جديدة تضم مجموعة موسعة من الأحجار والرخام.',
                'excerpt_en' => 'We\'re preparing to open a new showroom featuring an expanded collection of stone and marble.',
                'body_ar' => 'مسودة داخلية — سيتم نشر التفاصيل الكاملة قريبًا بعد تأكيد موعد الافتتاح الرسمي.',
                'body_en' => 'Internal draft — full details will be published soon once the official opening date is confirmed.',
                'is_published' => false,
                'published_at' => null,
                'category' => 'News',
                'tags' => ['Trends'],
                'search' => null,
            ],
            [
                'title_ar' => 'مسودة: تحديث ساعات العمل خلال شهر رمضان',
                'title_en' => 'Draft: Ramadan Working Hours Update',
                'excerpt_ar' => 'مسودة إعلان عن ساعات العمل المعدلة خلال شهر رمضان المبارك.',
                'excerpt_en' => 'Draft announcement of adjusted working hours during the holy month of Ramadan.',
                'body_ar' => 'مسودة داخلية قيد المراجعة — لم تُعتمد التفاصيل النهائية بعد.',
                'body_en' => 'Internal draft under review — final details not yet approved.',
                'is_published' => false,
                'published_at' => null,
                'category' => 'News',
                'tags' => ['Trends'],
                'search' => null,
            ],
        ];

        foreach ($posts as $post) {
            $category = BlogCategory::where('name_en', $post['category'])->first();
            $tagNames = $post['tags'];
            $search = $post['search'];
            unset($post['category'], $post['tags'], $post['search']);

            $record = Post::updateOrCreate(
                ['title_en' => $post['title_en']],
                $post + ['author_id' => $author?->id, 'blog_category_id' => $category?->id]
            );

            $tagIds = Tag::whereIn('name_en', $tagNames)->pluck('id');
            $record->tags()->sync($tagIds);

            // Skip live API calls during tests (see phpunit.xml APP_ENV=testing).
            if ($search && ! app()->environment('testing') && $record->getMedia('cover')->isEmpty()) {
                foreach ($fetcher->fetchPhotos($search, 'pexels', 1) as $path) {
                    $record->addMedia($path)->toMediaCollection('cover');
                }
            }
        }
    }
}
