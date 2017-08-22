<?php return [

    'file_name_generator' => function (\Reshadman\FileSecretary\Application\Usecases\PresentedFile $presentedFile) {
        // No new file is created if the same file is uploaded multiple times.
        $fileContents = $presentedFile->getFileContents();
        return crc32($fileContents) . '-' . md5($fileContents);
    },

    'persistence' => [

        'provider' => \Reshadman\FileSecretary\Infrastructure\EloquentPersistedFileProvider::class,

        'eloquent' => [

            'model' => \Reshadman\FileSecretary\Infrastructure\EloquentPersistedFile::class,

            'table' => 'system__files'
        ],

        'database' => [

            'table' => 'system__files'

        ]
    ],

    'available_image_templates' => [

    ],
    
    'contexts' => [

        'file_manager_private' => [

            'context_folder' => 'file_manager',

            'driver' => 'private',

            'driver_based_address' => null,

            'category' => \Reshadman\FileSecretary\Domain\ContextTypes::TYPE_BASIC_FILE,

            'privacy' => \Reshadman\FileSecretary\Domain\Privacy\NotAllowedPrivacy::class

        ],

        'file_manager_public' => [

            'context_folder' => 'file_manager',

            'driver' => 'public',

            'driver_based_address' => 'https://files.jobinja.ir/',

            'category' => \Reshadman\FileSecretary\Domain\ContextTypes::TYPE_BASIC_FILE,

            'privacy' => \Reshadman\FileSecretary\Domain\Privacy\PublicPrivacy::class

        ],

        'images_private' => [

            'driver' => 'private',

            'driver_base_address' => null,

            'context_folder' => \Reshadman\FileSecretary\Domain\ContextTypes::TYPE_IMAGE,

            'category' => 'images',

            'privacy' => \Reshadman\FileSecretary\Domain\Privacy\NotAllowedPrivacy::class
        ],

        'images_public' => [

            'driver' => 'public',

            'context_folder' => \Reshadman\FileSecretary\Domain\ContextTypes::TYPE_IMAGE,

            'driver_base_address' => 'https://images.jobinja.ir/',

            'category' => \Reshadman\FileSecretary\Domain\ContextTypes::TYPE_IMAGE,

            'privacy' => \Reshadman\FileSecretary\Domain\Privacy\PublicPrivacy::class

        ],

        'assets' => [

            'driver' => 'public',

            'context_folder' => 'assets',

            'driver_base_address' => 'https://assets.jobinja.ir/',

            'category' => \Reshadman\FileSecretary\Domain\ContextTypes::TYPE_ASSET,
        ]

    ],

    'asset_folders' => [

        'asset_1' => [

            'path' => __DIR__ . '/../../stub/asset_tags/asset_1',

            'context' => 'assets',

            'env_key' => 'ASSET_1_ID'
        ],

        'asset_2' => [

            'path' => __DIR__ . '/../../stub/asset_tags/asset_2',

            'context' => 'assets',

            'env_key' => 'ASSET_2_ID'
        ]

    ],

    'listen' => [

        \Reshadman\FileSecretary\Application\Events\AfterAssetUpload::class => [
            \Reshadman\FileSecretary\Application\AfterAssetUploadEventHandler::class
        ]

    ]
];