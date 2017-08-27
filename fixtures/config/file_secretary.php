<?php return [

    /*
    |--------------------------------------------------------------------------
    | File Name Generator
    |--------------------------------------------------------------------------
    |
    | An instance of the presented file is passed to this function,
    | You can use your own function to generate the name
    | By default the following function prevents redundant files with the
    | same content. Based on the file hash + file size. It is recommended
    | to keep the function as there is a low probability that
    | two different files exist with different contents but same file
    | size and sha1 hash of the content.
    |
    */
    'file_name_generator' => function (\Reshadman\FileSecretary\Application\PresentedFile $presentedFile) {
        $size = $presentedFile->getFileInstance()->getSize();
        $hash = sha1_file($presentedFile->getFileInstance()->getPath());
        return  $size . '-' . $hash;
    },

    /*
    |--------------------------------------------------------------------------
    | Database Tracking (Persistable Files)
    |--------------------------------------------------------------------------
    |
    | You can use the following eloquent model for tracking files
    | You can also extend it to have more flexibility.
    |
    */
    'eloquent' => [

        'model' => \Reshadman\FileSecretary\Application\EloquentPersistedFile::class,

        'table' => 'system__files'
    ],

    /*
    |--------------------------------------------------------------------------
    | Image templates
    |--------------------------------------------------------------------------
    |
    | You store your images with this package, it then handles the resizing and
    | manipulating based on the following templates, Each Intervention, Image
    | instance is passed to the template defined here.
    | by default a dynamic template takes care of generic needs. You can
    | implement your own template by extending the base template and
    | implementing the proper interface.
    | The package takes care of manipulating, storing and serving,
    | by default images are generated once, after that they can be served
    | without the participation of php with a simple nginx snippet included
    | in the package
    |
    */
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

    /*
    |--------------------------------------------------------------------------
    | Contexts
    |--------------------------------------------------------------------------
    | Contexts are used to have different strategies for each one of your needs
    | Basically there are three context category types:
    |   1. Basic Files: A Simple File Store which stores files in this format:
    |      context_folder/xxx-unique-folder-name-xxxxxxx.ext
    |   2. Images: This context category allows manipulating images, it stores
    |      images in this format:
    |      context_folder/xxxx-unique-folder-name-based-on-hash-xxx/main.(png|jpg|etc)
    |      Images are requested through the controller, the main file is found by the package
    |      It is passed to the proper template(based on file name) it is manipulated, it is
    |      It is then store beside the main image so it won't be regenerated again.
    |   3. Assets: After each deploy for purging cache you can use the
    |      php artisan file-secretary:upload-assets --tags=backoffice,c2c_assets,b2c_assets
    |      to publish your assets to the cloud, each call creates a new folder for the entire
    |      assets, and puts the proper env key in the .env file so there is
    |      no runtime i/o needed for fetching the new folder's unique key, unlike
    |      Other packages that store the key in a .json file and read it in the runtime.
    |
    |      The array key represents the "context name"
    | Keys:
    |   "driver"                : The laravel filesystem driver name.
    |   "context_folder"        : The folder which files are stored in your driver.
    |   "driver_base_address"   : For public files you can use the built in functions
    |                             To address the relative URIs generated by this package.
    |   "category"              : one of the "basic_file", "image" and "asset", Images will be manipulated
    |                             By the defined templates.
    |   "privacy"               : There are built in privacy strategies "public" and "no_access"
    |                             If you want to handle the file serving differently you can implement
    |                             Your own ones based on the proper interfaces of the package.
    */
    'contexts' => [

        'file_manager_private' => [
            'context_folder' => 'file_manager',
            'driver' => 'private',
            'driver_based_address' => null,
            'category' => \Reshadman\FileSecretary\Application\ContextCategoryTypes::TYPE_BASIC_FILE,
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

    /*
    |--------------------------------------------------------------------------
    | Asset folders
    |--------------------------------------------------------------------------
    |
    | You can use the same context for different assets
    | You may have some backoffice assets + c2c_assets + b2c_assets
    | You specify the proper folders for each of them they can be
    | easily addressed by simple function calls when you need them.
    |
    */
    'asset_folders' => [

        'asset_1' => [
            'path' => __DIR__ . '/../../stub/asset_tags/asset_1',
            'context' => 'assets',

            // You should specify the name of the env variable which stores the unique id
            'env_key' => 'ASSET_1_ID'
        ],

        'asset_2' => [
            'path' => __DIR__ . '/../../stub/asset_tags/asset_2',
            'context' => 'assets',
            'env_key' => 'ASSET_2_ID'
        ]

    ],

    /*
    |--------------------------------------------------------------------------
    | Listeners
    |--------------------------------------------------------------------------
    |
    | You can handle event handles here, you can use your own ones or remove them
    | entirely.
    |
    */
    'listen' => [

        // Triggered when an entire asset folder is uploaded to the cloud
        // By default it will erase every asset before the previous version.
        \Reshadman\FileSecretary\Application\Events\AfterAssetUpload::class => [
            \Reshadman\FileSecretary\Application\AfterAssetUploadEventHandler::class
        ]

    ],

    /*
    |--------------------------------------------------------------------------
    | Load routes
    |--------------------------------------------------------------------------
    |
    | By default two routes are created by this package.
    | One for serving basic files
    | and Second one for serving manipulable images.
    | If you don't want them you can implement your own.
    |
    */
    'load_routes' => true
];