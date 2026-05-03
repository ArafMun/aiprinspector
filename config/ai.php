<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AI Service Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for AI providers used in code review functionality.
    |
    */

    'default_provider' => env('AI_DEFAULT_PROVIDER', 'openai'),

    /*
    |--------------------------------------------------------------------------
    | Provider Settings
    |--------------------------------------------------------------------------
    |
    | Individual provider configurations are stored in config/services.php
    | but you can override them here if needed.
    |
    */

    'providers' => [
        'claude' => [
            'enabled' => env('CLAUDE_ENABLED', true),
            'max_tokens' => 4000,
            'timeout' => 30,
            'rate_limit' => [
                'requests_per_minute' => env('CLAUDE_RATE_LIMIT_RPM', 50),
                'requests_per_hour' => env('CLAUDE_RATE_LIMIT_RPH', 1000),
                'retry_after_seconds' => env('CLAUDE_RETRY_AFTER', 60),
            ],
        ],
        'openai' => [
            'enabled' => env('OPENAI_ENABLED', true),
            'max_tokens' => 4000,
            'timeout' => 30,
            'rate_limit' => [
                'requests_per_minute' => env('OPENAI_RATE_LIMIT_RPM', 60),
                'requests_per_hour' => env('OPENAI_RATE_LIMIT_RPH', 3500),
                'retry_after_seconds' => env('OPENAI_RETRY_AFTER', 60),
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Fallback Settings
    |--------------------------------------------------------------------------
    |
    | Configure fallback behavior when the primary provider fails.
    |
    */

    'fallback_enabled' => env('AI_FALLBACK_ENABLED', false),
    'fallback_provider' => env('AI_FALLBACK_PROVIDER', 'claude'),

    /*
    |--------------------------------------------------------------------------
    | Contact Email Configuration
    |--------------------------------------------------------------------------
    |
    | Email address where contact form submissions should be sent.
    | This makes it easy to change the contact email without modifying code.
    |
    */

    'contact_email' => env('CONTACT_EMAIL', 'arafatuddin.work@gmail.com'),

];
