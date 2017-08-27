<?php return [

    'file_name_generator' => function (\Reshadman\FileSecretary\Application\PresentedFile $presentedFile) {
        // This prevents multiple files with the same contents.
        // And it is too rare, to have two different files with the same hash and size.
        // You could also add an additional hash, but it will increase the filename size
        // Which may lead to some problems in Windows systems.
        $size = $presentedFile->getFileInstance()->getSize();
        $hash = sha1_file($presentedFile->getFileInstance()->getPath());
        return  $size . '-' . $hash;
    },


    'eloquent' => [

        'model' => \Reshadman\FileSecretary\Application\EloquentPersistedFile::class,

        'table' => 'system__files'
    ],

    'available_image_templates' => [
        'companies_logo_200x200' => [
            'class' => \Reshadman\FileSecretary\Infrastructure\Images\Templates\DynamicResizableTemplate::class,
            'args' => [
                'width' => 200,
                'height' => 200,
                'encodings' => null, // When null only parent file encoding is allowed.
                'strip' =>  false, // removes the ICC profile when imagick is used.
            ],
        ],
        'companies_logo_201xauto' => [
            'class' => \Reshadman\FileSecretary\Infrastructure\Images\Templates\DynamicResizableTemplate::class,
            'args' => [
                'width' => 201,
                'height' => null, // Height will be calculated automatically
                'mode' => \Reshadman\FileSecretary\Infrastructure\Images\TemplateManager::MODE_FIT, // The image will fit
                'encodings' => [
                    'png' // Ony png extension is served otherwise it throws 404 exception
                ]
            ],
        ],
    ],
    
    'contexts' => [
        // The array key is the context name
        'file_manager_private' => [

            // The folder, all basic files for this
            // context will be store in this folder of your laravel file driver in this format:
            // folder_name/xxxxx-xxxxxxxxxxxxxxxxxxxxxxxxxx.ext
            'context_folder' => 'file_manager',

            // You laravel file system driver name
            'driver' => 'private',

            // If you want you can specify a base address for your url,
            // In case of rackspace it can be something like the following:
            // https://YOUR_UNIQUE_RACKSPACE_PUBLIC_CDN_SUBDOMAIN.rackspace.com/xxxxxx/
            // When you call the address generator functions it will append the base address so you
            // have a full URL.
            'driver_based_address' => null,

            // The Context Category, you can have assets, image and basic files, image is used to resize images.
            'category' => \Reshadman\FileSecretary\Application\ContextCategoryTypes::TYPE_BASIC_FILE,

            // You can implement your own privacy class which will deny access if the file
            // is requested through the package's default file server controller.
            'privacy' => \Reshadman\FileSecretary\Application\Privacy\NotAllowedPrivacy::class
        ],
        'file_manager_public' => [
            'context_folder' => 'file_manager',
            'driver' => 'public',
            'driver_based_address' => 'https://files.jobinja.ir/',
            'category' => \Reshadman\FileSecretary\Application\ContextCategoryTypes::TYPE_BASIC_FILE,
            'privacy' => \Reshadman\FileSecretary\Application\Privacy\PublicPrivacy::class
        ],
        'images_private' => [
            'driver' => 'private',
            'driver_base_address' => null,
            'context_folder' => \Reshadman\FileSecretary\Application\ContextCategoryTypes::TYPE_IMAGE,
            'category' => 'images',
            'privacy' => \Reshadman\FileSecretary\Application\Privacy\NotAllowedPrivacy::class
        ],
        'images_public' => [
            'driver' => 'public',
            'context_folder' => \Reshadman\FileSecretary\Application\ContextCategoryTypes::TYPE_IMAGE,
            'driver_base_address' => 'https://images.jobinja.ir/',
            'category' => \Reshadman\FileSecretary\Application\ContextCategoryTypes::TYPE_IMAGE,
            'privacy' => \Reshadman\FileSecretary\Application\Privacy\PublicPrivacy::class
        ],
        'assets' => [
            'driver' => 'public',
            'context_folder' => 'assets',
            'driver_base_address' => 'https://assets.jobinja.ir/',
            'category' => \Reshadman\FileSecretary\Application\ContextCategoryTypes::TYPE_ASSET,
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