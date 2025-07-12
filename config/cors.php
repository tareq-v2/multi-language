<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', '*'], // Allow these paths
    'allowed_methods' => ['*'], // Allow all HTTP methods
    'allowed_origins' => ['*'], // Allow your React frontend
    'allowed_origins_patterns' => [], // Regex patterns for origins
    'allowed_headers' => ['*'], // Allow all headers
    'exposed_headers' => [], // Headers exposed to the client
    'max_age' => 0, // Cache duration for preflight requests
    'supports_credentials' => true, // Allow cookies, tokens, etc.
];

