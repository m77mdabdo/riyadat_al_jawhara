<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Service;
use App\Models\StoneType;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $installation = Service::where('name_en', 'Marble Installation')->first();
        $facade = Service::where('name_en', 'Facade Design')->first();
        $interior = Service::where('name_en', 'Interior Stone Decor')->first();

        $carrara = StoneType::where('name_en', 'Carrara Marble')->first();
        $hashemi = StoneType::where('name_en', 'Hashemi Stone')->first();
        $blackGranite = StoneType::where('name_en', 'Black Granite')->first();
        $quartz = StoneType::where('name_en', 'Engineered White Quartz')->first();

        $projects = [
            [
                'title_ar' => 'فيلا خاصة - حي الملقا',
                'title_en' => 'Private Villa - Al Malqa District',
                'description_ar' => 'تركيب أرضيات وسلالم رخام كرارا لفيلا سكنية فاخرة.',
                'description_en' => 'Installation of Carrara marble flooring and staircases for a luxury residential villa.',
                'location_ar' => 'الرياض',
                'location_en' => 'Riyadh',
                'client_name' => 'Al Malqa Residence',
                'service_id' => $installation?->id,
                'stone_type_id' => $carrara?->id,
                'completed_at' => '2025-11-10',
                'is_featured' => true,
            ],
            [
                'title_ar' => 'واجهة برج تجاري - حي الملز',
                'title_en' => 'Commercial Tower Facade - Al Malaz District',
                'description_ar' => 'تصميم وتنفيذ واجهة خارجية بحجر هاشمي لبرج تجاري من 8 طوابق.',
                'description_en' => 'Design and execution of a Hashemi stone exterior facade for an 8-story commercial tower.',
                'location_ar' => 'الرياض',
                'location_en' => 'Riyadh',
                'client_name' => 'Al Malaz Tower',
                'service_id' => $facade?->id,
                'stone_type_id' => $hashemi?->id,
                'completed_at' => '2025-08-22',
                'is_featured' => true,
            ],
            [
                'title_ar' => 'مطبخ فيلا - حي الشاطئ',
                'title_en' => 'Villa Kitchen - Al Shati District',
                'description_ar' => 'تنفيذ كاونتر مطبخ بكوارتز أبيض هندسي مقاوم للخدوش والبقع.',
                'description_en' => 'Execution of a kitchen countertop in scratch- and stain-resistant engineered white quartz.',
                'location_ar' => 'جدة',
                'location_en' => 'Jeddah',
                'client_name' => 'Private Client',
                'service_id' => $interior?->id,
                'stone_type_id' => $quartz?->id,
                'completed_at' => '2026-02-14',
                'is_featured' => false,
            ],
            [
                'title_ar' => 'أرضية معرض سيارات - الدمام',
                'title_en' => 'Car Showroom Flooring - Dammam',
                'description_ar' => 'تركيب أرضية جرانيت أسود لامع لمعرض سيارات فاخر.',
                'description_en' => 'Installation of glossy black granite flooring for a luxury car showroom.',
                'location_ar' => 'الدمام',
                'location_en' => 'Dammam',
                'client_name' => 'Premium Motors',
                'service_id' => $installation?->id,
                'stone_type_id' => $blackGranite?->id,
                'completed_at' => '2025-05-30',
                'is_featured' => false,
            ],
            [
                'title_ar' => 'واجهة مسجد - حي النرجس',
                'title_en' => 'Mosque Facade - Al Narjis District',
                'description_ar' => 'تصميم وتركيب واجهة حجرية تراثية لمسجد حي.',
                'description_en' => 'Design and installation of a heritage-style stone facade for a neighborhood mosque.',
                'location_ar' => 'الرياض',
                'location_en' => 'Riyadh',
                'client_name' => 'Al Narjis Mosque Committee',
                'service_id' => $facade?->id,
                'stone_type_id' => $hashemi?->id,
                'completed_at' => '2024-12-05',
                'is_featured' => true,
            ],
            [
                'title_ar' => 'صالة استقبال فندق - مكة المكرمة',
                'title_en' => 'Hotel Reception Lobby - Makkah',
                'description_ar' => 'ديكور داخلي بالرخام لصالة استقبال فندق خمس نجوم.',
                'description_en' => 'Interior marble decor for a five-star hotel reception lobby.',
                'location_ar' => 'مكة المكرمة',
                'location_en' => 'Makkah',
                'client_name' => 'Grand Makkah Hotel',
                'service_id' => $interior?->id,
                'stone_type_id' => $carrara?->id,
                'completed_at' => '2025-09-18',
                'is_featured' => false,
            ],
        ];

        foreach ($projects as $order => $project) {
            Project::updateOrCreate(
                ['title_en' => $project['title_en']],
                $project + ['is_active' => true, 'order' => $order + 1]
            );
        }
    }
}
