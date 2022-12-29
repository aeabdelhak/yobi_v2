<?php

use Illuminate\Http\Request;
$request = new Request();

$origin = '';

foreach (getallheaders() as $name => $value) {
    if (strtolower($name) == 'origin') {
        $origin = $value;
        break;
    }
}

return [
    'paths' => ['api/*', 'graphql'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['*'],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => false,
    'max_age' => false,
    'supports_credentials' => false,
];