<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'youdao' => [
        'appKey' => env('YOUDAO_APP_KEY'),
        'appSecret' => env('YOUDAO_APP_SECRET'),
    ],

    'kd100' => [
        'customer' => env('KD100_CUSTOMER'),
        'key' => env('KD100_KEY')
    ],

    'alicloud' => [
        'appCode' => env('ALICLOUD_APPCODE'), // APPCODE
    ],

    'ecjia_b2b2c' => [
        'key' => env('DSCMALL_AUTH_KEY'),

        'cipher' => 'AES-256-CBC',
    ],

    'lbs' => [
        'default' => 'qq',
        // 腾讯地图
        'qq' => [
            'driver' => 'tencent',
            // 开发密钥（Key）
            'key' => 'OB4BZ-D4W3U-B7VVO-4PJWW-6TKDJ-WPB77',
        ],
        'amap' => [
            'driver' => 'amap',
            // 开发密钥（Key）
            'key' => '9e63797e66f566e07c5ffe9bf318afd0',
        ],
        'baidu' => [
            'driver' => 'baidu',
            // 开发密钥（Key）
            'key' => '',
        ],
    ],
];
