<?php

namespace Database\Seeders;

use App\Models\Slider;
use Illuminate\Database\Seeder;

class SliderSeeder extends Seeder
{
    public function run(): void
    {
        $sliders = [
            [
                'title_ar' => 'الجودة تبدأ من الحجر',
                'title_en' => 'Quality Starts With Stone',
                'subtitle_ar' => 'توريد وتركيب وتصميم الأحجار الطبيعية والرخام الفاخر',
                'subtitle_en' => 'Supply, installation, and design of natural stone and luxury marble',
                'button_text_ar' => 'اطلب عرض سعر',
                'button_text_en' => 'Request a Quote',
                'button_link' => '/contact',
                'order' => 1,
            ],
            [
                'title_ar' => 'واجهات معمارية تدوم للأجيال',
                'title_en' => 'Architectural Facades Built to Last',
                'subtitle_ar' => 'تصاميم حجرية تجمع بين الفخامة والمتانة',
                'subtitle_en' => 'Stone designs that combine luxury and durability',
                'button_text_ar' => 'استعرض مشاريعنا',
                'button_text_en' => 'View Our Projects',
                'button_link' => '/projects',
                'order' => 2,
            ],
            [
                'title_ar' => 'حرفية دقيقة في كل تفصيلة',
                'title_en' => 'Precision Craftsmanship in Every Detail',
                'subtitle_ar' => 'فريق متخصص لتنفيذ رؤيتك بأعلى معايير الجودة',
                'subtitle_en' => 'A specialized team to execute your vision with the highest quality standards',
                'button_text_ar' => 'خدماتنا',
                'button_text_en' => 'Our Services',
                'button_link' => '/services',
                'order' => 3,
            ],
        ];

        foreach ($sliders as $slider) {
            Slider::updateOrCreate(['title_en' => $slider['title_en']], $slider + ['is_active' => true]);
        }
    }
}
