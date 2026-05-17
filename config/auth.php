<?php

use App\Models\Admin;
use App\Models\Store;
use App\Models\Staff;

return [

    'defaults' => [
        'guard' => 'admin',
        'passwords' => 'admins',
    ],

    'guards' => [
        'admin' => [
            'driver' => 'session',
            'provider' => 'admins',
        ],
        'store' => [
            'driver' => 'session',
            'provider' => 'stores',
        ],
        'staff' => [
            'driver' => 'session',
            'provider' => 'staff',
        ],
    ],

    'providers' => [
        'admins' => [
            'driver' => 'eloquent',
            'model' => Admin::class,
        ],
        'stores' => [
            'driver' => 'eloquent',
            'model' => Store::class,
        ],
        'staff' => [
            'driver' => 'eloquent',
            'model' => Staff::class,
        ],
    ],

    'passwords' => [
        'admins' => [
            'provider' => 'admins',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
        'stores' => [
            'provider' => 'stores',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
