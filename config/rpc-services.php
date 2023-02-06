<?php

return [
    'services' => [
        [
            'services' => [
                'TestService',
                'PickupService',
                'SystemService',
                'StoreService',
            ],
            'nodes' => [
                ['host' => 'cashier.ecjia.com', 'port' => 443, 'path' => '/sites/rpc/cashier-service'],
            ]
        ]
    ],

    'auth_user' => env('RPC_AUTH_USER'),

    'auth_password' => env('RPC_AUTH_PASSWORD'),
];
