<?php

/*
|--------------------------------------------------------------------------
| Site tuning
|--------------------------------------------------------------------------
|
| Limits, list sizes, cache lifetimes and upload sizes used across the site.
| Change them here, not in code.
|
*/

return [

    // Requests per minute per IP. Each limiter has its own counter.
    'throttle' => [
        'contact' => 5,
        'comments' => 5,
        'beacons' => 20,
        'catalogue' => 20,
        'search' => 60,
    ],

    // Items per page on paginated listings.
    'pagination' => [
        'projects' => 9,
        'posts' => 9,
        'gallery' => 24,
        'search' => 12,
    ],

    // Fixed-size lists.
    'lists' => [
        'home_featured_projects' => 6,
        'home_stones' => 8,
        'home_faqs' => 4,
        'related_projects' => 6,
        'related_posts' => 3,
        'search_per_type' => 50,
    ],

    // Seconds.
    'cache' => [
        'fragment_ttl' => 86400,
        'sitemap_ttl' => 3600,
    ],

    // Kilobytes.
    'uploads' => [
        'max_kb' => 5120,
        'branding_max_kb' => 2048,
    ],

    // Width in px of the WebP renditions generated for every uploaded image.
    'webp_widths' => [
        'card' => 800,
        'large' => 1600,
    ],

    // Admin dashboard window for the WhatsApp clicks stat.
    'dashboard_whatsapp_days' => 30,

    'notifications' => [
        // Queued notifications: attempts and seconds between retries.
        'tries' => 3,
        'backoff' => [60, 300, 900],
        // Daily follow-up digest (leads:follow-up-digest).
        'digest_time' => '08:00',
        'timezone' => 'Asia/Riyadh',
        // Language for mail to the company inbox (Settings → email).
        'company_locale' => 'ar',
    ],

];
