<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'name_ar' => 'توريد الحجر',
                'name_en' => 'Stone Supply',
                'icon' => 'heroicon-o-truck',
                'description_ar' => 'نوفر أجود أنواع الأحجار الطبيعية والرخام المستورد والمحلي بمواصفات دقيقة تناسب مشروعك.',
                'description_en' => 'We supply premium natural stone and marble, both imported and local, with precise specifications tailored to your project.',
                'order' => 1,
            ],
            [
                'name_ar' => 'تركيب الرخام',
                'name_en' => 'Marble Installation',
                'icon' => 'heroicon-o-wrench-screwdriver',
                'description_ar' => 'فريق فني متخصص في تركيب الرخام والحجر الطبيعي للأرضيات والجدران والواجهات بدقة واحترافية.',
                'description_en' => 'A specialized technical team for installing marble and natural stone on floors, walls, and facades with precision and professionalism.',
                'order' => 2,
            ],
            [
                'name_ar' => 'تصميم الواجهات الحجرية',
                'name_en' => 'Facade Design',
                'icon' => 'heroicon-o-building-office-2',
                'description_ar' => 'تصميم وتنفيذ واجهات حجرية عصرية تعكس الفخامة والهوية المعمارية لمشروعك.',
                'description_en' => 'Designing and executing modern stone facades that reflect luxury and the architectural identity of your project.',
                'order' => 3,
            ],
            [
                'name_ar' => 'ديكورات حجرية داخلية',
                'name_en' => 'Interior Stone Decor',
                'icon' => 'heroicon-o-sparkles',
                'description_ar' => 'حلول ديكور داخلي بالحجر الطبيعي والرخام، من المطابخ إلى الحمامات وأماكن الاستقبال.',
                'description_en' => 'Interior decor solutions using natural stone and marble, from kitchens to bathrooms and reception areas.',
                'order' => 4,
            ],
        ];

        foreach ($services as $service) {
            Service::updateOrCreate(['name_en' => $service['name_en']], $service + ['is_active' => true]);
        }
    }
}
