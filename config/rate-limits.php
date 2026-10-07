<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Rate Limits (requests per minute)
    |--------------------------------------------------------------------------
    |
    | Centralized throttle values. Override per-environment via .env.
    | Applied in routes/*.php as throttle:{value},1.
    |
    */

    'api' => (int) env('THROTTLE_API', 120),
    'api_login' => (int) env('THROTTLE_API_LOGIN', 10),
    'ai' => (int) env('THROTTLE_AI', 30),
    'contact' => (int) env('THROTTLE_CONTACT', 10),
    'widget' => (int) env('THROTTLE_WIDGET', 5),
];
