<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        Setting::updateOrCreate(
            ['id' => 1],
            [
                'phone' => '+966 55 123 4567',
                'whatsapp' => '966551234567',
                'email' => 'info@jra-stone.sa',
                'address_ar' => 'طريق الملك فهد، حي العليا، الرياض، المملكة العربية السعودية',
                'address_en' => 'King Fahd Road, Al Olaya District, Riyadh, Saudi Arabia',
                'facebook_url' => 'https://facebook.com/jra.stone',
                'instagram_url' => 'https://instagram.com/jra.stone',
                'tiktok_url' => 'https://tiktok.com/@jra.stone',
                'snapchat_url' => 'https://snapchat.com/add/jra.stone',
                'working_hours_ar' => 'السبت - الخميس: 9 صباحًا - 6 مساءً',
                'working_hours_en' => 'Sat - Thu: 9:00 AM - 6:00 PM',
                'about_ar' => 'ريادة الجوهرة الإبداعية (JRA) شركة متخصصة في توريد وتركيب وتصميم الأحجار الطبيعية والرخام، نخدم عملاءنا في جميع أنحاء المملكة العربية السعودية بأعلى معايير الجودة والدقة والإبداع.',
                'about_en' => 'Riyadat Al-Jawhara Al-Ibdaeya (JRA) is a company specialized in the supply, installation, and design of natural stone and marble, serving clients across Saudi Arabia with the highest standards of quality, precision, and creativity.',
                'vision_ar' => 'أن نكون الخيار الأول في المملكة لأعمال الحجر والرخام الفاخرة.',
                'vision_en' => 'To be the first choice in the Kingdom for premium stone and marble work.',
                'mission_ar' => 'تقديم حلول متكاملة من التوريد إلى التركيب والتصميم بجودة عالية والتزام تام بالمواعيد.',
                'mission_en' => 'Delivering end-to-end solutions from supply to installation and design, with high quality and strict adherence to deadlines.',
                'map_lat' => 24.7136,
                'map_lng' => 46.6753,
            ]
        );
    }
}
