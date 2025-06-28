<?php

return [
    'blade' => [
        /*
        |--------------------------------------------------------------------------
        | Blade Templating Activation
        |--------------------------------------------------------------------------
        |
        | Enable this if you want to use Blade as the templating engine. If set 
        | to false, Blade view rendering will be disabled.
        |
        */
        'is_active' => true,

        /*
        |--------------------------------------------------------------------------
        | Custom Blade Views Path
        |--------------------------------------------------------------------------
        |
        | Define a custom path for Blade views. By default, views are expected to 
        | be located in: Modules/{ModuleName}/Interface/Blades
        |
        */
        'path' => 'Blades',
    ],
    'react' => [
        /*
        |--------------------------------------------------------------------------
        | React Templating Activation (Cooming soon)
        |--------------------------------------------------------------------------
        |
        | Enable this if you want to use React as the templating engine. If set 
        | to false, React view rendering will be disabled.
        |
        */
        'is_active' => true,

        /*
        |--------------------------------------------------------------------------
        | Custom React Views Path
        |--------------------------------------------------------------------------
        |
        | Define a custom path for React views. By default, views are expected to 
        | be located in: Modules/{ModuleName}/Interface/Views
        |
        */
        'path' => 'Views',
    ],
    'middleware' => [
        /*
        |--------------------------------------------------------------------------
        | Authentication Middleware
        |--------------------------------------------------------------------------
        |
        | Enable this if you want to apply authentication middleware (e.g., 'auth') 
        | to your routes. Useful for protecting routes that require user login.
        |
        */
        'auth' => false,

        /*
        |--------------------------------------------------------------------------
        | API Middleware
        |--------------------------------------------------------------------------
        |
        | Enable this if you want to apply API middleware (e.g., 'api') to your 
        | routes. Suitable for routes intended for API access only.
        |
        */
        'api' => false,
    ],
];
