<?php return [

    'default' => 'public',

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root'   => __DIR__ . '../fixtures/',
        ],

        'public' => [
            'driver'    => 'rackspace',
            'username'  => env('RS_PUBLIC_USER'),
            'key'       => env('RS_PUBLIC_KEY'),
            'container' => env('RS_PUBLIC_CONTAINER'),
            'endpoint'  => 'https://identity.api.rackspacecloud.com/v2.0/',
            'region'    => env('RS_PUBLIC_REGION'),
            'url_type'  => 'publicURL',
        ],

        'private' => [
            'driver'    => 'rackspace',
            'username'  => env('RS_PRIVATE_USER'),
            'key'       => env('RS_PRIVATE_KEY'),
            'container' => env('RS_PRIVATE_CONTAINER'),
            'endpoint'  => 'https://identity.api.rackspacecloud.com/v2.0/',
            'region'    => env('RS_PRIVATE_REGION'),
            'url_type'  => 'publicURL',
        ]

    ],
];