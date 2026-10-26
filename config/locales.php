<?php

return [

    /*
    | Public-site languages. Every public page lives under /{locale}/...
    | (see routes/web.php); un-prefixed legacy URLs 301 to the default.
    */
    'supported' => ['ar', 'en'],

    /*
    | Default public language: used for "/" and for legacy redirects, and as
    | the hreflang x-default. Independent of APP_LOCALE, which only affects
    | the admin panel, CLI and queued emails.
    */
    'default' => 'ar',

];
