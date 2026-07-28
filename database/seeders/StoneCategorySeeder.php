<?php

namespace Database\Seeders;

use App\Models\StoneCategory;
use App\Models\StoneType;
use Illuminate\Database\Seeder;

class StoneCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name_ar' => 'رخام',
                'name_en' => 'Marble',
                'order' => 1,
                'types' => [
                    [
                        'name_ar' => 'رخام كرارا',
                        'name_en' => 'Carrara Marble',
                        'origin_ar' => 'مستورد - إيطاليا',
                        'origin_en' => 'Imported - Italy',
                        'description_ar' => 'رخام إيطالي فاخر بلون أبيض وعروق رمادية ناعمة، مثالي للأرضيات والواجهات الفاخرة.',
                        'description_en' => 'Luxury Italian marble with white tone and soft grey veining, ideal for premium floors and facades.',
                    ],
                    [
                        'name_ar' => 'رخام توسكانا',
                        'name_en' => 'Tuscany Marble',
                        'origin_ar' => 'مستورد - إيطاليا',
                        'origin_en' => 'Imported - Italy',
                        'description_ar' => 'رخام بيج دافئ يضفي طابعًا كلاسيكيًا وفخامة على المساحات الداخلية.',
                        'description_en' => 'Warm beige marble that adds a classic and luxurious touch to interior spaces.',
                    ],
                ],
            ],
            [
                'name_ar' => 'جرانيت',
                'name_en' => 'Granite',
                'order' => 2,
                'types' => [
                    [
                        'name_ar' => 'جرانيت أسود',
                        'name_en' => 'Black Granite',
                        'origin_ar' => 'مستورد - الهند',
                        'origin_en' => 'Imported - India',
                        'description_ar' => 'جرانيت أسود لامع عالي الصلابة، مناسب للمطابخ والأرضيات عالية الاستخدام.',
                        'description_en' => 'High-hardness glossy black granite, suitable for kitchens and high-traffic flooring.',
                    ],
                ],
            ],
            [
                'name_ar' => 'كوارتز',
                'name_en' => 'Quartz',
                'order' => 3,
                'types' => [
                    [
                        'name_ar' => 'كوارتز أبيض هندسي',
                        'name_en' => 'Engineered White Quartz',
                        'origin_ar' => 'محلي',
                        'origin_en' => 'Local',
                        'description_ar' => 'كوارتز هندسي عالي المقاومة للخدوش والبقع، خيار عملي وأنيق للمطابخ.',
                        'description_en' => 'Highly scratch- and stain-resistant engineered quartz, a practical and elegant choice for kitchens.',
                    ],
                ],
            ],
            [
                'name_ar' => 'حجر طبيعي',
                'name_en' => 'Natural Stone',
                'order' => 4,
                'types' => [
                    [
                        'name_ar' => 'حجر هاشمي',
                        'name_en' => 'Hashemi Stone',
                        'origin_ar' => 'محلي - السعودية',
                        'origin_en' => 'Local - Saudi Arabia',
                        'description_ar' => 'حجر طبيعي سعودي شهير للواجهات الخارجية، يجمع بين المتانة والمظهر التراثي.',
                        'description_en' => 'A well-known Saudi natural stone for exterior facades, combining durability with a heritage look.',
                    ],
                ],
            ],
        ];

        foreach ($categories as $index => $category) {
            $types = $category['types'];
            unset($category['types']);

            $cat = StoneCategory::updateOrCreate(['name_en' => $category['name_en']], $category);

            foreach ($types as $order => $type) {
                StoneType::updateOrCreate(
                    ['name_en' => $type['name_en']],
                    $type + [
                        'stone_category_id' => $cat->id,
                        'is_active' => true,
                        'order' => $order + 1,
                    ]
                );
            }
        }
    }
}
