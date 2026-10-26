<?php

namespace Database\Seeders;

use App\Models\StoneAttribute;
use App\Models\StoneType;
use Illuminate\Database\Seeder;

/**
 * DEMO ONLY (called from DemoSeeder). Neutral, industry-standard specification
 * values so the stone filters and spec lists have something to show. The
 * links between these values and the demo stones are illustrative -- the
 * company must set the real specs for its real products in the admin panel.
 */
class StoneAttributeSeeder extends Seeder
{
    /**
     * @var array<string, array<int, array{0: string, 1: string, 2?: string}>> type => [name_en, name_ar, hex?]
     */
    public const OPTIONS = [
        StoneAttribute::FINISH => [
            ['Polished', 'مصقول (لامع)'],
            ['Honed', 'مطفي'],
            ['Brushed', 'مفرّش'],
            ['Flamed', 'محروق (فليم)'],
            ['Sandblasted', 'مرمّل'],
            ['Leathered', 'جلدي'],
        ],
        StoneAttribute::COLOUR => [
            ['White', 'أبيض', '#F2F0EB'],
            ['Beige', 'بيج', '#D9C7A7'],
            ['Grey', 'رمادي', '#9C9A96'],
            ['Black', 'أسود', '#1F1F1F'],
            ['Brown', 'بني', '#7A5A3C'],
            ['Green', 'أخضر', '#4E6B4A'],
        ],
        StoneAttribute::SIZE => [
            ['2 cm slab', 'لوح 2 سم'],
            ['3 cm slab', 'لوح 3 سم'],
            ['60 × 60 cm tile', 'بلاط 60 × 60 سم'],
            ['60 × 120 cm tile', 'بلاط 60 × 120 سم'],
            ['Cut to size', 'تفصيل حسب الطلب'],
        ],
        StoneAttribute::APPLICATION => [
            ['Interior', 'داخلي'],
            ['Exterior', 'خارجي'],
            ['Flooring', 'أرضيات'],
            ['Wall cladding', 'تكسية جدران'],
            ['Countertops', 'أسطح مطابخ'],
            ['Stairs', 'درج'],
        ],
    ];

    /**
     * Illustrative demo links: stone name_en => [type => [option name_en...]].
     */
    private const DEMO_LINKS = [
        'Carrara Marble' => [
            'origin' => StoneType::ORIGIN_IMPORTED,
            StoneAttribute::FINISH => ['Polished', 'Honed'],
            StoneAttribute::COLOUR => ['White', 'Grey'],
            StoneAttribute::SIZE => ['2 cm slab', '3 cm slab', '60 × 60 cm tile'],
            StoneAttribute::APPLICATION => ['Interior', 'Flooring', 'Countertops'],
        ],
        'Tuscany Marble' => [
            'origin' => StoneType::ORIGIN_IMPORTED,
            StoneAttribute::FINISH => ['Polished', 'Honed'],
            StoneAttribute::COLOUR => ['Beige'],
            StoneAttribute::SIZE => ['2 cm slab', '60 × 120 cm tile'],
            StoneAttribute::APPLICATION => ['Interior', 'Flooring', 'Wall cladding'],
        ],
        'Black Granite' => [
            StoneAttribute::FINISH => ['Polished', 'Flamed', 'Leathered'],
            StoneAttribute::COLOUR => ['Black'],
            StoneAttribute::SIZE => ['2 cm slab', '3 cm slab', 'Cut to size'],
            StoneAttribute::APPLICATION => ['Interior', 'Exterior', 'Countertops', 'Stairs'],
        ],
        'Engineered White Quartz' => [
            StoneAttribute::FINISH => ['Polished'],
            StoneAttribute::COLOUR => ['White'],
            StoneAttribute::SIZE => ['2 cm slab', '3 cm slab'],
            StoneAttribute::APPLICATION => ['Interior', 'Countertops'],
        ],
        'Hashemi Stone' => [
            StoneAttribute::FINISH => ['Brushed', 'Sandblasted'],
            StoneAttribute::COLOUR => ['Beige', 'Brown'],
            StoneAttribute::SIZE => ['Cut to size'],
            StoneAttribute::APPLICATION => ['Exterior', 'Wall cladding'],
        ],
    ];

    public function run(): void
    {
        $byType = [];

        foreach (self::OPTIONS as $type => $options) {
            foreach ($options as $order => $option) {
                $byType[$type][$option[0]] = StoneAttribute::updateOrCreate(
                    ['type' => $type, 'name_en' => $option[0]],
                    ['name_ar' => $option[1], 'hex_color' => $option[2] ?? null, 'order' => $order, 'is_active' => true],
                );
            }
        }

        foreach (self::DEMO_LINKS as $stoneName => $links) {
            $stone = StoneType::where('name_en', $stoneName)->first();

            if (! $stone) {
                continue;
            }

            if (isset($links['origin'])) {
                $stone->update(['origin_type' => $links['origin']]);
            }

            $ids = collect(array_keys(StoneAttribute::TYPES))
                ->flatMap(fn (string $type) => collect($links[$type] ?? [])->map(fn (string $name) => $byType[$type][$name]->id))
                ->all();

            $stone->stoneAttributes()->syncWithoutDetaching($ids);
        }
    }
}
