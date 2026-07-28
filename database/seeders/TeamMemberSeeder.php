<?php

namespace Database\Seeders;

use App\Models\TeamMember;
use Illuminate\Database\Seeder;

class TeamMemberSeeder extends Seeder
{
    public function run(): void
    {
        $members = [
            [
                'name_ar' => 'خالد الزهراني',
                'name_en' => 'Khalid Al-Zahrani',
                'position_ar' => 'المدير العام',
                'position_en' => 'General Manager',
                'phone' => '+966 55 111 2222',
                'email' => 'khalid@jra-stone.sa',
                'order' => 1,
            ],
            [
                'name_ar' => 'عبدالله الشمري',
                'name_en' => 'Abdullah Al-Shammari',
                'position_ar' => 'مدير المشاريع',
                'position_en' => 'Projects Manager',
                'phone' => '+966 55 222 3333',
                'email' => 'abdullah@jra-stone.sa',
                'order' => 2,
            ],
            [
                'name_ar' => 'ريم القحطاني',
                'name_en' => 'Reem Al-Qahtani',
                'position_ar' => 'مصممة داخلية',
                'position_en' => 'Interior Designer',
                'phone' => '+966 55 333 4444',
                'email' => 'reem@jra-stone.sa',
                'order' => 3,
            ],
            [
                'name_ar' => 'ماجد العتيبي',
                'name_en' => 'Majed Al-Otaibi',
                'position_ar' => 'رئيس فريق التركيب',
                'position_en' => 'Installation Team Lead',
                'phone' => '+966 55 444 5555',
                'email' => 'majed@jra-stone.sa',
                'order' => 4,
            ],
        ];

        foreach ($members as $member) {
            TeamMember::updateOrCreate(['name_en' => $member['name_en']], $member + ['is_active' => true]);
        }
    }
}
