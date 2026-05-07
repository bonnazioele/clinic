<?php

return [
    'enabled' => env('SEMAPHORE_ENABLED', false),

    'api_key' => env('SEMAPHORE_API_KEY'),

    'sender_name' => env('SEMAPHORE_SENDER_NAME'),

    'base_url' => env('SEMAPHORE_BASE_URL', 'https://semaphore.co/api/v4'),

    'priority' => env('SEMAPHORE_PRIORITY', false),

    'timeout' => env('SEMAPHORE_TIMEOUT', 60),

    'connect_timeout' => env('SEMAPHORE_CONNECT_TIMEOUT', 30),
];