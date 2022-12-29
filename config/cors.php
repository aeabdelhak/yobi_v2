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


    'paths' => ['api/*', 'graphql', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [$origin],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 2123123,

    'supports_credentials' => false,

];