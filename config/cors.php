<?php

return [
    'paths'               => ['api/*', 'livewire/*'],
    'allowed_methods'     => ['GET', 'POST'],
    'allowed_origins'     => [env('APP_URL', 'http://localhost')],
    'allowed_origins_patterns' => [],
    'allowed_headers'     => ['Content-Type', 'X-Requested-With', 'X-CSRF-TOKEN', 'X-XSRF-TOKEN'],
    'exposed_headers'     => [],
    'max_age'             => 0,
    'supports_credentials'=> true,
];
