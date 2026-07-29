<?php

namespace Database\Seeders;

// STOCK PHOTO — images below are sourced live from Pexels/Pixabay via
// App\Services\StockMediaFetcher (free for commercial use, no attribution
// required per their license terms as of 2026-07). Replace with real JRA
// project/site photography before final handoff.

use App\Models\Slider;
use App\Services\StockMediaFetcher;
use Illuminate\Database\Seeder;

class SliderSeeder extends Seeder
{
    public function run(StockMediaFetcher $fetcher): void
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
                'search' => 'marble architecture luxury',
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
                'search' => 'modern stone facade building',
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
                'search' => 'natural stone quarry',
            ],
        ];

        foreach ($sliders as $slider) {
            $search = $slider['search'];
            unset($slider['search']);

            $record = Slider::updateOrCreate(['title_en' => $slider['title_en']], $slider + ['is_active' => true]);

            // Skip live API calls during tests (see phpunit.xml APP_ENV=testing).
            if (! app()->environment('testing') && $record->getMedia('image')->isEmpty()) {
                foreach ($fetcher->fetchPhotos($search, 'pexels', 1) as $path) {
                    $record->addMedia($path)->toMediaCollection('image');
                }
            }
        }
    }
}
