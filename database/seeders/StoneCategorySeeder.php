<?php

namespace Database\Seeders;

// STOCK PHOTO — stone type cover/gallery images below are sourced live from
// Pexels/Pixabay via App\Services\StockMediaFetcher (free for commercial use,
// no attribution required per their license terms as of 2026-07). Replace
// with real photos of JRA's actual stone inventory before final handoff.

use App\Models\StoneCategory;
use App\Models\StoneType;
use App\Services\StockMediaFetcher;
use Illuminate\Database\Seeder;

class StoneCategorySeeder extends Seeder
{
    public function run(StockMediaFetcher $fetcher): void
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
                        'search' => 'polished white marble surface',
                    ],
                    [
                        'name_ar' => 'رخام توسكانا',
                        'name_en' => 'Tuscany Marble',
                        'origin_ar' => 'مستورد - إيطاليا',
                        'origin_en' => 'Imported - Italy',
                        'description_ar' => 'رخام بيج دافئ يضفي طابعًا كلاسيكيًا وفخامة على المساحات الداخلية.',
                        'description_en' => 'Warm beige marble that adds a classic and luxurious touch to interior spaces.',
                        'search' => 'beige marble texture',
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
                        'search' => 'black granite texture',
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
                        'search' => 'white quartz countertop',
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
                        'search' => 'beige natural stone facade texture',
                    ],
                ],
            ],
        ];

        foreach ($categories as $category) {
            $types = $category['types'];
            unset($category['types']);

            $cat = StoneCategory::updateOrCreate(['name_en' => $category['name_en']], $category);

            foreach ($types as $order => $type) {
                $search = $type['search'];
                unset($type['search']);

                $stoneType = StoneType::updateOrCreate(
                    ['name_en' => $type['name_en']],
                    $type + [
                        'stone_category_id' => $cat->id,
                        'is_active' => true,
                        'order' => $order + 1,
                    ]
                );

                // Skip live API calls during tests (see phpunit.xml APP_ENV=testing).
                if (! app()->environment('testing') && $stoneType->getMedia('cover')->isEmpty()) {
                    $photos = $fetcher->fetchPhotos($search, 'pexels', 3);

                    if ($photos !== []) {
                        $stoneType->addMedia(array_shift($photos))->toMediaCollection('cover');
                    }

                    foreach ($photos as $photo) {
                        $stoneType->addMedia($photo)->toMediaCollection('gallery');
                    }
                }
            }
        }
    }
}
