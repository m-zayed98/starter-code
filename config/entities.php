<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Project Entities Configuration
    |--------------------------------------------------------------------------
    |
    | Define all project entities with their respective routes file,
    | namespace for controllers, resources, and requests.
    |
    */

    'user' => [
        'routes' => 'api.php',
        'namespace' => 'User',
        'prefix' => 'users',
        'middleware' => ['api'],
    ],

    'admin' => [
        'routes' => 'admin.php',
        'namespace' => 'Admin',
        'prefix' => 'admins',
        'middleware' => ['api'],
    ],

    /*
    |--------------------------------------------------------------------------
    | API Versioning
    |--------------------------------------------------------------------------
    |
    | Configure API versioning prefix
    |
    */

    'api_version' => 'v1',
];
