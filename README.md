# Laravel File Secretary
![Build Status](http://img.shields.io/travis/reshadman/file-secretary/master.png?style=flat-square)
![Build Status](https://www.versioneye.com/user/projects/59a41045368b08003f172a41/badge.png?style=flat-square)

Get rid of anything related to files in Laravel, This package handles all for you.

## What does this package do?
 1. **Handles your public assets** (.css, .js, .etc) you can use your CDN provider to serve the static assets. with a simple function call. For instance you can serve your static files through Rackspace CDN. After each deploy a new unique id is assigned to the path so the cached assets would be purged.
 
 2. **Image manipulation and storage**: Store all of your images in the cloud (based on Laravel Adapters, Rackspace, S3, minio etc.), The image resizing is handled with simple configuration. You define templates and then images are generated automatically. Once a new image template created, you can use the nginx directives included in the package to remove the participation of PHP in next calls. Read the documentation for more info. A simple, fast and reliable method to manipulate images that are stored in the cloud.
 3. **Detects redundant files**, File names are generated based on the 
 filesize + a hash function.
 so redundant files could not exist technically, 
 You can implement your own file name generator, too.
 4. **Storing files** with a simple method call. 
 They can be served without the participation of PHP, and they can
 be addressed with the package's helper functions if they are public.
 5. **Database Tracking** (Optional), 
 Centerialized Eloquent model which tracks your stored files(and images), You can store files/resiable-images and attach them to your bussiness models. Then you can use them. If it is an Image the templates are accessible by simple getters in the model.
 6. **Simple helper functions** for dealing with resizable image urls, file urls, 
 asset urls etc.
 7. **A Simple controller for serving private/public files** Serve both resizable images, and files.
 You can implement your own access control for restricting access on request.
 So for instance if the file should be only served to its uploader, you can implement an access controller which checks that the requested file is attached to the user model or not.

## Getting Started

 - [Does this package fit my needs?](#does-this-package-fit-my-needs)
 - [Installation](#installation)
 - [Configuration](#configuration)
 - [Usage](#usage)
    - [Terminology](#terminology) : read for faster understanding.
    - [Defining Contexts](#defining-contexts)
    - [Using the asset uploader](#using-the-asset-uploader)
    - [Storing files](#storing-files)
        - [File names](#file-names)
        - [Storing Service(Command)](#storing-command)
        - [Storable file with file path](#storable-file-with-file-path)
        - [Storable file with file content](#storable-file-with-file-content)
        - [Storable file with file instance](#storable-file-with-file-instance)
        - [Storable file with file HTTP url](#storable-file-with-file-http-url)
        - [Stored File Resoponse](#stored-file-response)
    - [Storing images](#storing-images)
    - [Deleting files](#deleting-files)
    - [Storing Eloquent-tracked files](#storing-eloquent-tracked-files)
        - [When to use tracked files](#when-to-use-tracked-files)
        - [Storing simple files](#storing-simple-files)
        - [Storing manipulatable images](#storing-manipulatable-images)
        - [Using your own model](#using-your-own-model)
    - [Deleting Eloquent-tracked files](#deleting-eloquent-tracked-files)
        - [Handle what happens on delete](#handle-what-happens-on-delete)
    - [Manipulating images](#manipulating-images)
        - [Image Templates](#image-templates)
        - [Using the dynamic generic template](#using-the-dynamic-generic-template)
        - [Writing your own template](#writing-your-own-template)
        - [Storing manipulated images](#storing-manipulated-images)
    - [Serving files](#serving-files)
        - [Default Routes](#default-routes)
        - [Serving simple files](#serving-simple-files)
        - [Serving images](#serving-images)
            - [Serving original images](#serving-original-images)
            - [Serving manipulated images](#serving-manipulated-images)
            - [Performance optimizations](#performance-optimizations)
        - [Restricting access](#restricting-access)
        - [Add your custom routes](#add-your-custom-routes)
    - [Helper functions](#helper-functions)
    - [Production Notes](#production-notes)
        - [Best Practices](#best-practices)
        - [Nginx Directives](#nginx-directives)
  - [Limitations](#limitations)



## Does this package fit my needs?
<https://12factor.net> offers some practical specs for dealing with files, called *attached resources*.
As files are an important part of the most of the information systems, they should be stored
in a reliable, fast third party service (Like Amazon S3, or Rackspace object storage).

![Attached Resources](https://12factor.net/images/attached-resources.png)

So if your application domain is not about files (You are not Dropbox or Amazon S3 itself :D).
You can follow the spec and use the features this package offers, There are typically 
some main usecases for files:
 - Serving private/public files (Like PDF, Docs etc)
 - Serving manipulatable images (Images that should be re-sized, watermarked etc),
 which is computation/memory heavy.
 - Serving static assets (like css, js, svg) etc.
 - Attaching and tracking business domain files to their equivalent business model (Like the profile image of a user)

Laravel file-secretary offers some simple solutions for the above needs.
We did not apart the package to individual ones to respect the simplicity. The 
interface that this package offers could be much simpler and more performant as Laravel
file-secretary has been developed periodically it is not in its simplest shape. We will keep that
in mind for next releases.

## Installation
Run the following command in your project directory, to add the package to `composer.json`:
```bash
composer require reshadman/file-secretary ">=1.0.0 <1.1.0"
```

Add the Service Provider to your `config/app.php`
```php
<?php
return [
    // other app.php config elements
    'providers' => [
        // Other providers
        // ...
        \Reshadman\FileSecretary\Infrastructure\FileSecretaryServiceProvider::class    
    ]  
];
```

Then publish the configuration file:
```bash
php artisqan vendor:publish \
    --provider=Reshadman\FileSecretary\Infrastructure\FileSecretaryServiceProvider \
    --tag=config
```

If you want to use the eloquent model for attaching files to your models, export the migration:
```bash
php artisqan vendor:publish \
    --provider=Reshadman\FileSecretary\Infrastructure\FileSecretaryServiceProvider \
    --tag=migrations
```

## Configuration
Almost everything is handled by configuration, For understanding how this package works please read the documentation
blocks in the default config file here:

[config/file_secretary.php](https://github.com/reshadman/file-secretary/blob/master/fixtures/config/file_secretary.php)


## Usage
The best way to see the usage is by reading the integration tests, however you may
read the following doc to understand what it does.

## Terminology
**Contexts**: file-secretary uses contexts for detecting where to store the files based on laravel filesystem drivers,
we have four context categories `basic_file`, `image`, `manipulated_image` and `assets`,
all contexts should have a laravel filesystem driver, and a folder name in the driver.
When you command to store the file in a context, the equivalent, laravel disk driver is found by the config
and the starting path(folder of the context) is considered as the directory. Also generating file URLs is handled by this config.

**Context Category: `basic_file`**: a basic file is a simple file that does not need
any manipulation, when defining contexts you can indicate a `basic_file` context,
and when you command to store the file, they will be added in the context's laravel filesystem
driver and the given folder. Files can be served with or without the participation of PHP.


**Context Category: `image`**: Images that should be manipulated and mutated based on your given config. 
Storing manipulateable images is not different from storing simple files, except that
instead of a unique file name, they are stored in a unique directory, 
so the main image and its manipulated children are always in that unique folder.
You can also have different context strategies for the main image and its manipulated children.
You can indicate that the manipulated images of the main image should not be stored at all or be stored
beside the main image, or be stored in a different context (as manipulated images are not critical they can 
be stored in more cost effective storage like your own server).
 
**Context Category `manipulated_image`**: This is a context that is used for `image` context as the place
to store its manipulated images.

**Context Category: `asset`**: If you want, you can upload your entire built asset directory to your cloud CDN provider,
like Rackspace's public CDN.

**Asset Folders/Tags**: These are the folders that you want to upload to the cloud, in your blade templates
by calling `fs_asset('assetFolderName', 'your_local_path_to.css')` you can address them, assets are purged on each call, so the
browser won't serve the old versions.

**Image templates**: Templates are objects that keep the responsibility for mutating and manipulating images
You may use the default generic template (which dynamically re-sizes, strips images with different config), or 
implement your own one. They are defined in the config.

**Database/Eloquent Tracked Files**: After storing a file in a context you may assign it to the centralized eloquent
model, this model can be attached to other business models, like the profile image of a user.

**File/Folder name**: in the context of this package, a file name is a unique id
which is generated automatically, it needs to be unique in the context of your app.
You can implement your own file name generator. By default it is based on the `sha1(fContent) + filesize`, It guarantees
That the same file is not redundant in the context.

**Serving Files/Images/Manipulated Images**: Simply you may serve files publicly or privately, There is an HTTP endpoint in this package
which will serve the requested files/images, It retrieves the equivalent `context` and `file_name` from the requested URL,
and downloads the file from the storage of the found context and serves it to the user.
Before downloading, It calls the privacy object of the found context, if it returns false, it will throw an HTTP
400 Exception. You can define your privacy classes for the found context, which will be discussed in the documentation.






## Defining Contexts
You can read the default `config('file_secretary.contexts')` element of the config file to see all the available
options for creating contexts.

> Context must be unique in terms of Laravel filesystem disks + the starting folder in the disk (the `context_folder`).




## Using the asset uploader
To serve your static assets through a public CDN, like Rackspace public CDN, you create an asset context like below:
```php
<?php return [
    // Other file secretary config elements...
    'contexts' => [
            // Other contexts...
            'assets_context' => [
                'category' => \Reshadman\FileSecretary\Application\ContextCategoryTypes::TYPE_ASSET,
                'driver' => 'rackspace_asset_disk',
                'context_folder' => 'some_context_folder',
                'driver_base_address' => 'some-unique-string.rackcdn.com/etc/',
            ]
    ],
    
    'asset_folders' => [
        'backoffice' => [
            'path' => public_path('backoffice-assets'),
            'context' => 'assets_context',
            // fills .env automatically like => BACKOFFICE_ASSET_ID=unique-id
            // which causes to the browser to not serve old versions.
            'env_key' => 'BACKOFFICE_ASSET_ID',
        ],    
    ]
];
```
Other things will be handled automatically, in development environment, the assets will be served
local and in production env they will be served from the given `driver_base_address`

To sync the latest assets run:
```bash
php artisan file-secretary:upload-assets --tags=backoffice
```

To address the assets in the template call:
```php
<?php
$url = fs_asset('backoffice', 'styles.dist.css');

dump($url);
// In development env: http://localhost:8000/backoffice/styles.dist.css
// In production env: some-unique-id.rackcdn.com/[context_folder]/[latest-unique-id]/styles.dist.css
```

To delete the old versions:
By default the last two version are kept, and other versions are deleted after a successful upload.
For a different strategy you may change the following event listener in `file_secretary.php`:

```php
<?php
return [
    // Other config file_secretary config elements
    'listeners' => [
        \Reshadman\FileSecretary\Application\Events\AfterAssetUpload::class => [
            '\YourListener\Class',    
        ]  
    ],  
];
```


## Storing Files

file-secretary takes care of storing files after they have been validated by you. Then they can be tracked and served.

You can pass different file targets to the store command. For storing a file you should create an instance of the 
following class:

```php
<?php

\Reshadman\FileSecretary\Application\PresentedFile::class;
```

To see the list of available file targets read the contents of the above class.

> The `PresentedFile` class support different file types inluding: *URL*, *File Path*, *File Content* and *File Instance*
> ,read the following docs.


### File names
You can not control the file names, they are used for tracking files and images.

For the files of a `basic_file` context, the storing format would be something like this:

```bash
https://driver_base_address.mycdn.com/[context_folder]/[unique-id-xxxx-xxxxx].[file_extension]
```

For the files of a `image` or `manipulated_image` context the file name would be some thing like this:
```bash
# Main file
https://driver_base_address.mycdn.com/[context_folder]/[unique-id-xxxx-xxxx]/1_main.[image_extension]

# Manipulated templates:
https://driver_base_address.mycdn.com/[context_folder]/[unique-id-xxxx-xxxx]/[my_template_200x200.[image_extension_or_template_extension]
```

You can define your own unique id generator in the config:
```php
<?php 
return [
    'file_name_generator' => function (\Reshadman\FileSecretary\Application\PresentedFile $presentedFile) {
        $size = $presentedFile->getFileInstance()->getSize();
        $hash = sha1_file($presentedFile->getFileInstance()->getPath());
        return  $size . '-' . $hash;
    },
      
    // Other config elements...
];
```

>Please note that your closure should always return a unique string.

>The function prevents redundant files in the same context.


### Storing command
After you have created the `PresentedFile` instance you should pass it to the store command.
For knowing all the constructor parameters of the `PresentedFile` class read the class implementation.

For finalizing the storage read the following:

```php
<?php

$presentedFile = new \Reshadman\FileSecretary\Application\PresentedFile(
    'file_manager_private', // context name
    'SOME_TEXT_CONTENT_HERE_', // The mime type will be detected automatically.
    \Reshadman\FileSecretary\Application\PresentedFile::FILE_TYPE_CONTENT
);

/** @var \Reshadman\FileSecretary\Application\Usecases\StoreFile $storeCommand */
$storeCommand = app(\Reshadman\FileSecretary\Application\Usecases\StoreFile::class);

$addressableRemoteFile = $storeCommand->execute($presentedFile);

dd($addressableRemoteFile->fullRelative(), $addressableRemoteFile->fullUrl());

```

### Storable file with file path
If you have the a local file path, you can create the `PresentedFile` instance like the following:

```php
<?php

$presentedFile = new \Reshadman\FileSecretary\Application\PresentedFile(
    'file_manager_private', // context name
    $path = '../path_to/my_file.png',
    \Reshadman\FileSecretary\Application\PresentedFile::FILE_TYPE_PATH,
    basename($path)  
);
```

### Storable file with file content
If you have the file content, you can create the `PresentedFile` instance like the following:

```php
<?php

$presentedFile = new \Reshadman\FileSecretary\Application\PresentedFile(
    'file_manager_private', // context name
    file_get_contents($path = '../path_to/my_file.png'), // The mime type will be detected automatically.
    \Reshadman\FileSecretary\Application\PresentedFile::FILE_TYPE_CONTENT
);
```

### Storable file with file instance
If you want to store the file from an instance of request `UploadedFile` or a Symfony file instance
read the following

```php
<?php

$presentedFile = new \Reshadman\FileSecretary\Application\PresentedFile(
    'file_manager_private', // context name
    request()->file('company_logo'), // The mime type will be detected automatically.
    \Reshadman\FileSecretary\Application\PresentedFile::FILE_TYPE_INSTANCE
);
```


### Storable file with file HTTP url
If you want to store a file from a URL you can read the following:

```php
<?php

$presentedFile = new \Reshadman\FileSecretary\Application\PresentedFile(
    'file_manager_private', // context name
    'https://logo_url.com/logo.png', // The mime type will be detected automatically.
    \Reshadman\FileSecretary\Application\PresentedFile::FILE_TYPE_URL
);
```

### Storable file with file HTTP url
If you want to store a file from a base64 string read the following:

```php
<?php

$presentedFile = new \Reshadman\FileSecretary\Application\PresentedFile(
    'file_manager_private', // context name
    'base64encodecontent=', // The mime type will be detected automatically.
    \Reshadman\FileSecretary\Application\PresentedFile::FILE_TYPE_BASE64
);
```

>Note that if other meta data is attached to the string it should be removed by you, the mime type
will be detected automatically.



### Stored File Response
After executing the store file command it will return an instance of:

```php
<?php
\Reshadman\FileSecretary\Application\AddressableRemoteFile::class;
```

for knowing methods read the class implementation.

## Storing Images
Storing images is not different from storing files. You should only pass the proper
`context_name` which has `image` category to the `PresentedFile` instance.



#### 1. Uploading Purgeable Assets
```bash
php artisan file-secretary:upload-assets --tags=asset_1,asset_2
```

For using this feature you should:
 1. Create a context with `asset` category in the `contexts` section of the config file.
 2. Create an asset folder with proper config in the `asset_folders` section
 of the config file.
 
The asset_1 and asset_2 options in **--tags=asset_1,asset_2** represent the name
of the asset folders which should be defined in the config file.



### 2. Storing Basic And Image Files
For this feature you should:
1. Create a context of type `"basic_file"` in the contexts section

To Store a file you should create an instance of:

```
Reshadman\FileSecretary\Application\PresentedFile
```

and pass it to the ```StoreFile``` command.
See the example below:
```php
<?php

use Reshadman\FileSecretary\Application\PresentedFile;
use Reshadman\FileSecretary\Application\Usecases\StoreFile;

/** @var StoreFile $store */
$store = app(StoreFile::class);

$fileWithPath = new PresentedFile(
    'context_name',
    '/path/to/file',
    PresentedFile::FILE_TYPE_PATH,
    "optional_original_file_name.pdf"
);

$fileWithContent = new PresentedFile(
    'context_name',
    'this is a file content which the mime will be detected auto.',
    PresentedFile::FILE_TYPE_CONTENT
);

$fileWithUrl = new PresentedFile(
    'context_name',
    'https://path_to_file_with_url.com/logo.png',
    PresentedFile::FILE_TYPE_URL
);

$fileBase64 = new PresentedFile(
    'context_name',
    'base_64_encoded_content=',
    PresentedFile::FILE_TYPE_BASE64
);

$fileWithUploaded = new PresentedFile(
    'image_context_name',
    request()->file('company_logo'),
    PresentedFile::FILE_TYPE_INSTANCE
);

$fileWithSymfonyFile = new PresentedFile(
    'context_name',
    new \Symfony\Component\HttpFoundation\File\File("/path_to_file.pdf"),
    PresentedFile::FILE_TYPE_INSTANCE
);

/** @var \Reshadman\FileSecretary\Application\AddressableRemoteFile $response */
$response = $store->execute($fileWithPath);

dd($response->fullRelative(), $response->fullUrl());

```


#### 3. Manipulating Images
For this feature you should:
1. Create a context of type `"image"` in the contexts section
of the config file.
2. Create your needed templates in the `available_image_templates` of the config file.

Templates should implement the following interface:
```php
<?php
\Reshadman\FileSecretary\Infrastructure\Images\TemplateInterface::class;
```

You can use the default template for most of the use cases, if you need yours
you can see just how the following template works:
```php
<?php
\Reshadman\FileSecretary\Infrastructure\Images\Templates\DynamicResizableTemplate::class;
```

Storing images is not different from storing basic files you should only pass the 
proper "context", The image will be stored in the following format:

```
context_folder/xxxx-xxxxxx-unique-folder-name-based-on-file-name-generator/main.png```
```

Manipulating happens on the fly, for instance when the following url is called:
```
https://jobinja.ir/file-secretary/images/company_assets/xxxxx-xxxxxxx/c_logo_200x200.png
```

The controller find the `main.png` file from the sibling folder, finds the template
based on the requested file name and passes it to the proper template class.
The output image is stored beside the main file. and the response is served to the user.

**Serving without the participation of PHP once created:**

Assume that you are using Rackspace object storage, each container has a base address.
We will use our domain: `https://images.myapp.com` the domain is pointed
to an nginx config which will first try to get the file from the rackspace and as a fallback
will call our script endpoint which manipulates the image.
With this strategy the first call will be a not found in rackspace, the second one
will serve the file from the rackspace, you can also cache files in nginx for 
reducing the cost.

> In further releases, for files in the database(tracked files) we can store
the name of the template that we have manipulated. So when generating
the full url for that file we can decide to serve the rackspace file directly
or from our application proxy, which will add the template to the database
after the first call. So in the next calls the image is directly downloaded from Rackspace.

### Running the Integration Tests
 There are integration tests written for this package. To run integration
tests do as the following:

 1. Create your `phpunit.xml` file based on the packages's `phpunit.dist.xml`: `cp phpunit.dist.xml phpunit.xml`
 
 2. Fill the phpunit config with your environment variables.
 The package has been tested with **Rackspace** Object storage, to prove the 
 functionality in cloud. You can change the `phpunit.xml` file and the configs in `fixtures/config/`
 to integrate them with your testing environment.
 3. Run the tests with `vendor/bin/phpunit --debug`
 
> Currently there is no isolated object unit testing for this package. 
> They will be added in next releases.

### Package Roadmap
 1. Writing more integration tests + isolated object unit tests.
 2. Use more semantic names for features, class names and methods names.
 3. Make the tracking, eloquent independent.
 4. Refactor the code both for design and performance.
 5. In a new release, use a polymorphic model for database tracked files which allows
 to indicate that whether a file has been used somewhere in the other models or not.
  Which in result we can delete unused tracked files. This also works only with SQL databases.

### About the package
This package has been extracted from [*jobinja.ir - The leading job board and career platform in Iran*](https://jobinja.ir),
This is part of the work for making [jobinja.ir](https://jobinja.ir), [12factor.net](http://12factor.net) compatible.

### License
The MIT License (MIT). Please see License File for more information.
