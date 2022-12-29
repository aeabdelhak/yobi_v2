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

    'paths' => ['api/*','graphql/*', 'sanctum/csrf-cookie', 'storage/*', 'api'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [$origin],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 2123123,

    'supports_credentials' => true,

];