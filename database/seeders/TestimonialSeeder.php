<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    public function run(): void
    {
        $villa = Project::where('title_en', 'Private Villa - Al Malqa District')->first();
        $tower = Project::where('title_en', 'Commercial Tower Facade - Al Malaz District')->first();
        $hotel = Project::where('title_en', 'Hotel Reception Lobby - Makkah')->first();

        $testimonials = [
            [
                'client_name' => 'Faisal Al-Otaibi',
                'rating' => 5,
                'comment_ar' => 'فريق JRA نفذ أرضية الرخام بدقة عالية والتزام تام بالمواعيد. جودة استثنائية.',
                'comment_en' => 'The JRA team executed the marble flooring with great precision and full commitment to deadlines. Exceptional quality.',
                'project_id' => $villa?->id,
                'order' => 1,
            ],
            [
                'client_name' => 'Sarah Al-Ghamdi',
                'rating' => 5,
                'comment_ar' => 'واجهة البرج فاقت توقعاتنا، تصميم راقٍ وتنفيذ احترافي من البداية للنهاية.',
                'comment_en' => 'The tower facade exceeded our expectations — an elegant design and professional execution from start to finish.',
                'project_id' => $tower?->id,
                'order' => 2,
            ],
            [
                'client_name' => 'Mohammed Al-Harbi',
                'rating' => 4,
                'comment_ar' => 'تعامل احترافي وأسعار منافسة، وننصح بالتعامل معهم لأي مشروع حجر أو رخام.',
                'comment_en' => 'Professional dealings and competitive prices — we recommend them for any stone or marble project.',
                'project_id' => null,
                'order' => 3,
            ],
            [
                'client_name' => 'Grand Makkah Hotel Management',
                'rating' => 5,
                'comment_ar' => 'ديكور الرخام في صالة الاستقبال أضاف لمسة فخامة حقيقية لفندقنا.',
                'comment_en' => 'The marble decor in our reception lobby added a truly luxurious touch to our hotel.',
                'project_id' => $hotel?->id,
                'order' => 4,
            ],
        ];

        foreach ($testimonials as $testimonial) {
            Testimonial::updateOrCreate(
                ['client_name' => $testimonial['client_name'], 'comment_en' => $testimonial['comment_en']],
                $testimonial + ['is_active' => true]
            );
        }
    }
}
