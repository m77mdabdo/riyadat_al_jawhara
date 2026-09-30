<?php

return [

    /*
    | Days to keep the IP address and user agent stored with comments and
    | leads. After that, `privacy:prune` (scheduled daily) sets them to NULL.
    | The comment or lead itself is kept.
    */
    'retention_days' => (int) env('PRIVACY_RETENTION_DAYS', 180),

];
