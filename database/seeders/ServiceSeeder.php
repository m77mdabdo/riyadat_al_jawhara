<?php

namespace Database\Seeders;

// STOCK PHOTO — cover images below are sourced live from Pexels via
// App\Services\StockMediaFetcher (free for commercial use, no attribution
// required per their license terms as of 2026-07). Replace with real JRA
// project/site photography before final handoff.

use App\Models\Service;
use App\Services\StockMediaFetcher;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(StockMediaFetcher $fetcher): void
    {
        $services = [
            [
                'name_ar' => 'توريد الحجر',
                'name_en' => 'Stone Supply',
                'icon' => 'heroicon-o-truck',
                'description_ar' => 'نوفر أجود أنواع الأحجار الطبيعية والرخام المستورد والمحلي بمواصفات دقيقة تناسب مشروعك.',
                'description_en' => 'We supply premium natural stone and marble, both imported and local, with precise specifications tailored to your project.',
                'order' => 1,
                'search' => 'natural stone quarry slabs',
            ],
            [
                'name_ar' => 'تركيب الرخام',
                'name_en' => 'Marble Installation',
                'icon' => 'heroicon-o-wrench-screwdriver',
                'description_ar' => 'فريق فني متخصص في تركيب الرخام والحجر الطبيعي للأرضيات والجدران والواجهات بدقة واحترافية.',
                'description_en' => 'A specialized technical team for installing marble and natural stone on floors, walls, and facades with precision and professionalism.',
                'order' => 2,
                'search' => 'marble installation worker',
            ],
            [
                'name_ar' => 'تصميم الواجهات الحجرية',
                'name_en' => 'Facade Design',
                'icon' => 'heroicon-o-building-office-2',
                'description_ar' => 'تصميم وتنفيذ واجهات حجرية عصرية تعكس الفخامة والهوية المعمارية لمشروعك.',
                'description_en' => 'Designing and executing modern stone facades that reflect luxury and the architectural identity of your project.',
                'order' => 3,
                'search' => 'modern stone building facade',
            ],
            [
                'name_ar' => 'ديكورات حجرية داخلية',
                'name_en' => 'Interior Stone Decor',
                'icon' => 'heroicon-o-sparkles',
                'description_ar' => 'حلول ديكور داخلي بالحجر الطبيعي والرخام، من المطابخ إلى الحمامات وأماكن الاستقبال.',
                'description_en' => 'Interior decor solutions using natural stone and marble, from kitchens to bathrooms and reception areas.',
                'order' => 4,
                'search' => 'interior stone decor luxury',
            ],
        ];

        foreach ($services as $service) {
            $search = $service['search'];
            unset($service['search']);

            $record = Service::updateOrCreate(['name_en' => $service['name_en']], $service + ['is_active' => true]);

            // Skip live API calls during tests (see phpunit.xml APP_ENV=testing).
            if (! app()->environment('testing') && $record->getMedia('cover')->isEmpty()) {
                foreach ($fetcher->fetchPhotos($search, 'pexels', 1) as $path) {
                    $record->addMedia($path)->toMediaCollection('cover');
                }
            }
        }
    }
}
