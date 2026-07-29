<?php

namespace Database\Seeders;

// STOCK PHOTO — photo avatars below are sourced live from Pexels via
// App\Services\StockMediaFetcher (free for commercial use, no attribution
// required per their license terms as of 2026-07). These are NOT real
// photos of JRA's actual staff -- replace with real team photos before
// final handoff.

use App\Models\TeamMember;
use App\Services\StockMediaFetcher;
use Illuminate\Database\Seeder;

class TeamMemberSeeder extends Seeder
{
    public function run(StockMediaFetcher $fetcher): void
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
                'search' => 'professional business man headshot suit',
            ],
            [
                'name_ar' => 'عبدالله الشمري',
                'name_en' => 'Abdullah Al-Shammari',
                'position_ar' => 'مدير المشاريع',
                'position_en' => 'Projects Manager',
                'phone' => '+966 55 222 3333',
                'email' => 'abdullah@jra-stone.sa',
                'order' => 2,
                'search' => 'professional business man portrait office',
            ],
            [
                'name_ar' => 'ريم القحطاني',
                'name_en' => 'Reem Al-Qahtani',
                'position_ar' => 'مصممة داخلية',
                'position_en' => 'Interior Designer',
                'phone' => '+966 55 333 4444',
                'email' => 'reem@jra-stone.sa',
                'order' => 3,
                'search' => 'professional business woman headshot office',
            ],
            [
                'name_ar' => 'ماجد العتيبي',
                'name_en' => 'Majed Al-Otaibi',
                'position_ar' => 'رئيس فريق التركيب',
                'position_en' => 'Installation Team Lead',
                'phone' => '+966 55 444 5555',
                'email' => 'majed@jra-stone.sa',
                'order' => 4,
                'search' => 'professional man construction engineer portrait',
            ],
        ];

        foreach ($members as $member) {
            $search = $member['search'];
            unset($member['search']);

            $record = TeamMember::updateOrCreate(['name_en' => $member['name_en']], $member + ['is_active' => true]);

            // Skip live API calls during tests (see phpunit.xml APP_ENV=testing).
            if (! app()->environment('testing') && $record->getMedia('photo')->isEmpty()) {
                foreach ($fetcher->fetchPhotos($search, 'pexels', 1) as $path) {
                    $record->addMedia($path)->toMediaCollection('photo');
                }
            }
        }
    }
}
