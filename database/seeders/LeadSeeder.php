<?php

namespace Database\Seeders;

use App\Models\Lead;
use App\Models\Service;
use App\Models\StoneType;
use Illuminate\Database\Seeder;

class LeadSeeder extends Seeder
{
    public function run(): void
    {
        $installation = Service::where('name_en', 'Marble Installation')->first();
        $facade = Service::where('name_en', 'Facade Design')->first();
        $carrara = StoneType::where('name_en', 'Carrara Marble')->first();
        $hashemi = StoneType::where('name_en', 'Hashemi Stone')->first();

        $leads = [
            [
                'name' => 'Nasser Al-Dosari',
                'phone' => '+966 50 111 9999',
                'email' => 'nasser.d@example.com',
                'service_id' => $installation?->id,
                'stone_type_id' => $carrara?->id,
                'city' => 'Riyadh',
                'message' => 'أرغب بعرض سعر لتركيب رخام كرارا في صالة المنزل، المساحة تقريبًا 80 متر مربع.',
                'status' => 'new',
                'source' => 'website',
            ],
            [
                'name' => 'Lama Al-Sabhan',
                'phone' => '+966 50 222 8888',
                'email' => null,
                'service_id' => $facade?->id,
                'stone_type_id' => $hashemi?->id,
                'city' => 'Riyadh',
                'message' => 'Interested in a Hashemi stone facade for a 3-story building. Please advise on cost.',
                'status' => 'contacted',
                'source' => 'whatsapp',
            ],
            [
                'name' => 'Yousef Al-Mutairi',
                'phone' => '+966 50 333 7777',
                'email' => 'yousef.m@example.com',
                'service_id' => $installation?->id,
                'stone_type_id' => null,
                'city' => 'Dammam',
                'message' => 'أحتاج تركيب أرضيات جرانيت لمعرض تجاري صغير.',
                'status' => 'in_progress',
                'source' => 'website',
            ],
        ];

        foreach ($leads as $lead) {
            Lead::updateOrCreate(['name' => $lead['name'], 'phone' => $lead['phone']], $lead);
        }
    }
}
