<?php

/*
| Optional, consent-gated analytics. Leave everything empty and analytics is
| completely off: no banner, no scripts, no CSP changes.
|
| Nothing third-party is loaded until the visitor accepts the consent banner
| (PDPL). Conversion events sent: lead_submitted, whatsapp_click, phone_click.
*/

return [

    // Google Analytics 4 measurement ID, e.g. "G-XXXXXXXXXX".
    'ga4_id' => env('ANALYTICS_GA4_ID'),

    // Plausible: the site domain as registered in Plausible, e.g. "example.sa".
    'plausible_domain' => env('ANALYTICS_PLAUSIBLE_DOMAIN'),

    // Plausible script URL (change for a self-hosted Plausible).
    'plausible_src' => env('ANALYTICS_PLAUSIBLE_SRC', 'https://plausible.io/js/script.js'),

];
