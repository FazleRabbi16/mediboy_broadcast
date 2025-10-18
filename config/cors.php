<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'], // Specify your API paths
    'allowed_methods' => ['*'], // Specify allowed HTTP methods
    'allowed_origins' => [
        'https://admin.mediboy.org',
        'https://p.mediboy.org',
        'https://www.mediboy.org',
        'https://mediboyuser.vercel.app',
        'http://localhost:5173',
        'http://localhost:3000',
        'http://localhost:5174',
    ], // Allowed origins
    'allowed_origins_patterns' => [], // Patterns for allowed origins (optional)
    'allowed_headers' => ['*'], // Specify allowed headers
    'exposed_headers' => [], // Any headers you want to expose to the browser
    'max_age' => 0, // Maximum age of preflight request in seconds
    'supports_credentials' => true, // Indicates whether the request can include user credentials



];
