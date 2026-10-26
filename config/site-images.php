<?php

/*
|--------------------------------------------------------------------------
| Static site imagery (one manifest)
|--------------------------------------------------------------------------
|
| Every static image slot on the public site: where it's used, what to search
| for, the size budget, and which candidate was chosen. Files live in
| public/images/site/{slot}.webp.
|
| To swap an image: open docs/visual-review/index.html, pick another candidate
| ID for the slot, then run
|
|     php artisan site-images:pick {slot} {candidate-id}
|
| (candidates must still be in storage/app/stock-candidates/{slot}/; fetch
| them again with `php artisan site-images:candidates {slot}` if needed).
|
| The pick command records the choice (candidate ID, source, photographer...)
| in resources/data/site-images.json and regenerates docs/IMAGE_CREDITS.md.
|
| Rules (docs/MASTER_TASK.md §10c): no flag, emblem, Vision 2030 logo, holy
| sites, identifiable faces or brand names; never used as "our projects".
*/

return [

    // Where the chosen-image state and generated docs live (overridable in tests).
    'state_path' => resource_path('data/site-images.json'),
    'credits_path' => base_path('docs/IMAGE_CREDITS.md'),
    'review_path' => base_path('docs/visual-review/index.html'),

    'slots' => [
        'hero' => [
            'used' => 'Home hero background (when no slide has its own image)',
            'query' => 'white marble interior sunlight luxury',
            'max_kb' => 350,
            'text_space' => true,
        ],
        'about' => [
            'used' => 'About page banner',
            'query' => 'marble slabs stone warehouse',
            'max_kb' => 250,
            'text_space' => false,
        ],
        'texture-marble' => [
            'used' => 'Page-header band + Marble category tile (texture)',
            'query' => 'white marble texture',
            'max_kb' => 120,
            'max_width' => 1280,
            'text_space' => true,
        ],
        'texture-granite' => [
            'used' => 'Granite category tile + testimonials background (texture)',
            'query' => 'granite stone surface texture',
            'max_kb' => 120,
            'max_width' => 1280,
            'text_space' => true,
        ],
        'texture-sandstone' => [
            'used' => 'CTA background + Natural Stone category tile (texture)',
            'query' => 'beige sandstone wall texture',
            'max_kb' => 120,
            'max_width' => 1280,
            'text_space' => true,
        ],
        'texture-quartz' => [
            'used' => 'Quartz category tile (texture)',
            'query' => 'white quartz countertop texture',
            'max_kb' => 120,
            'max_width' => 1280,
            'text_space' => true,
        ],
        'heritage-jeddah' => [
            'used' => 'Heritage-in-stone strip: Jeddah Al-Balad coral stone (inspiration)',
            'query' => 'Jeddah Al Balad historic building',
            'max_kb' => 250,
            'text_space' => false,
        ],
        'heritage-diriyah' => [
            'used' => 'Heritage-in-stone strip: Diriyah (inspiration)',
            'query' => 'Diriyah Saudi Arabia',
            'max_kb' => 250,
            'text_space' => false,
        ],
        'heritage-alula' => [
            'used' => 'Heritage-in-stone strip: AlUla sandstone (inspiration)',
            'query' => 'AlUla sandstone rock desert',
            'max_kb' => 250,
            'text_space' => false,
        ],
        'heritage-najdi' => [
            'used' => 'Heritage-in-stone strip: Najdi architecture (inspiration)',
            'query' => 'Masmak fortress Riyadh',
            'max_kb' => 250,
            'text_space' => false,
        ],
    ],

    /*
    | Stone-category landing tiles on the home page: category slug => texture
    | slot, used until the category has its own image (Phase 11).
    */
    'category_tiles' => [
        'marble' => 'texture-marble',
        'granite' => 'texture-granite',
        'quartz' => 'texture-quartz',
        'natural-stone' => 'texture-sandstone',
    ],

];
