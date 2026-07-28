<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $faqs = [
            [
                'question_ar' => 'ما هي المدة اللازمة لتركيب الرخام في مشروع سكني؟',
                'question_en' => 'How long does marble installation take for a residential project?',
                'answer_ar' => 'تختلف المدة حسب مساحة المشروع، وعادة تتراوح بين أسبوع إلى ثلاثة أسابيع للمشاريع السكنية المتوسطة.',
                'answer_en' => 'The duration varies depending on the project size, typically ranging from one to three weeks for medium-sized residential projects.',
                'order' => 1,
            ],
            [
                'question_ar' => 'هل تقدمون خدمة المعاينة والتصميم المجاني؟',
                'question_en' => 'Do you offer free site inspection and design consultation?',
                'answer_ar' => 'نعم، نوفر معاينة ميدانية واستشارة تصميم أولية مجانية لجميع طلبات عروض الأسعار.',
                'answer_en' => 'Yes, we provide a free on-site inspection and initial design consultation for all quote requests.',
                'order' => 2,
            ],
            [
                'question_ar' => 'هل يمكنكم توريد أحجار مستوردة حسب الطلب؟',
                'question_en' => 'Can you supply imported stone on request?',
                'answer_ar' => 'نعم، نتعامل مع موردين موثوقين لاستيراد الرخام والحجر من إيطاليا والهند وغيرها حسب احتياج العميل.',
                'answer_en' => 'Yes, we work with trusted suppliers to import marble and stone from Italy, India, and elsewhere based on client needs.',
                'order' => 3,
            ],
            [
                'question_ar' => 'ما هي مناطق التغطية لخدماتكم؟',
                'question_en' => 'Which areas do your services cover?',
                'answer_ar' => 'نغطي جميع مناطق المملكة العربية السعودية، مع تركيز رئيسي على الرياض وجدة والدمام.',
                'answer_en' => 'We cover all regions of Saudi Arabia, with a primary focus on Riyadh, Jeddah, and Dammam.',
                'order' => 4,
            ],
            [
                'question_ar' => 'هل يوجد ضمان على أعمال التركيب؟',
                'question_en' => 'Is there a warranty on installation work?',
                'answer_ar' => 'نعم، نقدم ضمانًا على جودة التركيب يصل إلى سنتين حسب نوع المشروع.',
                'answer_en' => 'Yes, we provide an installation quality warranty of up to two years depending on the project type.',
                'order' => 5,
            ],
        ];

        foreach ($faqs as $faq) {
            Faq::updateOrCreate(['question_en' => $faq['question_en']], $faq + ['is_active' => true]);
        }
    }
}
