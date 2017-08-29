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
    - [Updating files](#updating-files)
    - [Manipulating images](#manipulating-images)
        - [Image Templates](#image-templates)
        - [Using the dynamic generic template](#using-the-dynamic-generic-template)
        - [Writing your own template](#writing-your-own-template)
        - [Storing manipulated image](#storing-manipulated-image)
    - [Storing Eloquent-tracked files](#storing-eloquent-tracked-files)
        - [When to use tracked files](#when-to-use-tracked-files)
        - [Storing simple files and manupulateable images](#storing-simple-files-and-manipulatable-images)
        - [Storing manipulatable images](#storing-manipulatable-images)
        - [Using your own model](#using-your-own-model)
    - [Deleting Eloquent-tracked files](#deleting-eloquent-tracked-files)
        - [Handle what happens on delete](#handle-what-happens-on-delete)
    - [Serving files](#serving-files)
        - [Default Routes](#default-routes)
        - [Restricting access](#restricting-access)
        - [Serving public files and manipulated images](#serving-public-files-and-manipulated-images)
        - [Serving images without the participation of PHP](#serving-images-without-the-participation-of-php)
    - [Helper functions](#helper-functions)
    - [Nginx Directives And Production Notes](#nginx-directives-and-production-notes)



## Does this package fit my needs?
<https://12factor.net> offers some practical specs for dealing with files, called *attached resources*.
As files are an important part of the most of the information systems, they should be stored
in a reliable, fast third party service (Like Amazon S3, or Rackspace object storage).

This package is an implementation of an spec for making Laravel application 12factor compatible for attached resources.
You can follow the spec with your own implementation.

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


new PresentedFile(
    'context_name',
    '/path/to/file',
    PresentedFile::FILE_TYPE_PATH,
    "optional_original_file_name.pdf"
);


## Deleting files
To delete a file you should use the following service command:

```php
<?php

/** @var \Reshadman\FileSecretary\Application\Usecases\DeleteFile $deleter */
$deleter = app(\Reshadman\FileSecretary\Application\Usecases\DeleteFile::class);

$fileFullRelativePath = '/context_folder/unique-id.pdf';

$deleter->execute("context_name", $fileFullRelativePath);

```

>If your deleting an image context and it stores templates, the manipulated images with be deleted, too.

## Updating files
When dealing with cloud file storages, which some of them offer CDN services, it is not a good idea
to update a file, you can simply delete the old one and create a new one, that is because it takes some times
that the CDN provider fetch the new version for all edges. And because we do not focus on file names 
in our implementation it is better to leave the file in the storage, or add a `X-DELETE-AFTER` header to that,
or delete it entirely and create a new file.
If you have some reason that this functionality is needed please create a PR or open an issue.

> Please not that if you implement your own update strategy, do not use the built in file name generator
function, because the file name is based on file content, so if you change the file content with the same
file name, it will be overridden next time the old file is uploaded to the context.
 

## Manipulating images
After you have created a context with `category` , `image` 
file-secretary allows to manipulate and mutate images based on image templates, 
you define image templates in the config file, and when the image is requested through the url
with predefined parameters, the main parent image is fetched from the disk storage, the image content
is passed to the template object and the template's result is served to the user.

The image templates are defined for all `image` contexts.


### Image templates 
To define your image templates you should add your config to the `available_image_templates` of the config file:

```php
<?php return [
  'companies_logo_200x200' => [
    'class' => \Reshadman\FileSecretary\Infrastructure\Images\Templates\DynamicResizableTemplate::class,
    'args' => [
      'width' => 200,
      'height' => 200,
      'encodings' => null, // When null only parent file encoding is allowed.
      'strip' =>  false, // removes the ICC profile when imagick is used.
    ],
  ],  
];
```

### Using the dynamic generic template
By using the following class as the ```class``` parameter of your image template config, you can control the 
behaviour with args, this will cover most of your needs:
```php
<?php 
\Reshadman\FileSecretary\Infrastructure\Images\Templates\DynamicResizableTemplate::class
```

The `class` parameter is the template implementation, each template can have some arguments, 
the `DynamicResiableTemplate` get some arguments which allow you to satisfy most of your needs.

for resizing:
 - Width: can be null or an integer, if null it will be automatically calculated
 - Height: can be nul or an integer, if null it will be automatically calculated
 - encodings: can be null or an array of images extensions like `['png', 'jpg', 'svg']`, if null
  the same encoding as the parent image will be used.
 - quality: takes an integer from 1 to 100
 - strip: when true and using imagick the embedded ICC profile of the image can removed, this reduces the generated image
 size significantly, the ICC profile is used to generate same true colors on different displys.
 - mode: You can use different fit strategies take a look at template manager class (```TemplateManager``) for 
  list of available fit strategies.

### Writing your own template
file-secretary uses intervention package for image manipulation.
To write your own template you should create a class that implements the following interface

```php
<?php
\Reshadman\FileSecretary\Infrastructure\Images\TemplateInterface::class;
```
Then you can use it in the config, also you can check the `DynamicResiableTemplate` implementation for faster understanding.


### Storing manipulated image
Manipulated images are requested through some URL, this URL is automatically generated by the 
package if for instance your attaching the `profile_image` to `users` model in eloquent.

The URL carries the following info with itself:
 - The context name
 - The context folder
 - Main image folder
 - Requested image template
 - Requested image extension
 
The endpoint retrieves the above info from the request and creates the following object
```php
<?php

\Reshadman\FileSecretary\Application\PrivacyCheckNeeds::class;
```

The object contains all the needed information for fetching the parent image,
manipulating it, storing it, and serving the manipulated image.

After the image has been manipulated, the context config says to whether store the manipulated image
in the cloud, or ignore it, or store it somewhere else, in a less expensive disk,
all of these can be defined by setting the value of `store_manipulated` in the config arary of the context.

There are 3 available values for `store_manipuled`:
 1. `false` : It won't store the manipulated image.
 2. `true`: Stores it beside the manipulated image.
 3. `another_context_name` : the target context should be of type `ContextCategoryTypes::TYPE_MANIPULATED_IMAGE`
 so the parent file for example is stored in Amazon S3, all the manipulated images are stored in your own FTP
 server which is wrapped around an Nginx proxy. This allows to serve manipulated images without the participation
 of PHP, once they are created, and as they are not the only data source, nothing happens when they are deleted 
 accidentally.
 
For more info about this options read the `contexts` section of the default config file.


## Storing Eloquent-tracked files

If your domain models (like your `User` model) has some blob files, for example the avatar of the user,
file-secretary offers a centralized, file model which keeps tracks of your cloud files.

So after you uploaded your file to the proper context you may create a tracked record for that and 
attach the record to your user's model through a foreign key.

You should also include the packages migration to create the database table.

### When to use tracked files
Anytime you need to attach a cloud file to an eloquent business model,
 - Your blog post has some image which need thumbnail resizing.
 - Your user has some documents 
 - etc.

The eloquent model has some getter methods that generate the image template URLs and file URLs, automatically.

For more info view the exported migration of the package.

### Storing simple files and manipulatable images
When storing files the process is not different from storing the file in the cloud,

You create an instance of `PresentedFile` and pass it to the following service, the created record is returned instead:

```php
<?php

$presentedFile = new \Reshadman\FileSecretary\Application\PresentedFile(
    'user_images_context',
    request()->file('avatar_file'),
    \Reshadman\FileSecretary\Application\PresentedFile::FILE_TYPE_INSTANCE,
    basename(request()->file('avatar_file')) // Is stored in the table so you can show the client's name if needed.
);

/** @var \Reshadman\FileSecretary\Application\Usecases\StoreTrackedFile $storeTrackedFile */
$storeTrackedFile = app(\Reshadman\FileSecretary\Application\Usecases\StoreTrackedFile::class);

$trackedModel = $storeTrackedFile->execute($presentedFile);

auth()->user()->avatar()->attach($trackedModel);

dump($trackedModel->full_url);

echo "<img src='{$trackedModel->image_templates['avatar_50x50']}' title='{$trackedModel->original_name}'/>";

```

### Using your own model
By default the following implementation is used:
```php
<?php
\Reshadman\FileSecretary\Application\EloquentPersistedFile::class;
```

You can extend the above class and create your own implementation:

```php
<?php return [
    // Other file_secretary.php config elements
    'eloquent' => [
        'model' => '\YourOwnModel\Class',
        'table' => 'defined_table_in_model_or_this_string_in_fallback'
    ]  
];
```

## Deleting Eloquent-tracked files
The package does not delete the file from cloud when you delete the tracked record, this is for preventing
unexpected behaviour.

You can delete the file manually by the following service:
```php
<?php

/** @var \Reshadman\FileSecretary\Application\EloquentPersistedFile $trackedFile */
$trackedFile = auth()->user()->avatar;

/** @var \Reshadman\FileSecretary\Application\Usecases\DeleteTrackedFile $deleter */
$deleter = app(\Reshadman\FileSecretary\Application\Usecases\DeleteTrackedFile::class);

$deleter->execute($trackedFile);

// You can also delete a file by uuid
$deleter->execute($trackedFile->getFileableUuid());
```

### Handle what happens on delete

There 3 strategies when calling the delete service:
 - Ignore deleting the remote file
 - Strict Delete of the remote file
 - Delete if the current unique id of the file does not exists in the same context

To indicate this you should execute the service like the following:
```php
<?php

use Reshadman\FileSecretary\Application\Usecases\DeleteTrackedFile;

/** @var \Reshadman\FileSecretary\Application\EloquentPersistedFile $trackedFile */
$trackedFile = auth()->user()->avatar;

/** @var \Reshadman\FileSecretary\Application\Usecases\DeleteTrackedFile $deleter */
$deleter = app(\Reshadman\FileSecretary\Application\Usecases\DeleteTrackedFile::class);

$deleter->execute($trackedFile, DeleteTrackedFile::ON_DELETE_DELETE_REMOTE);

$deleter->execute($trackedFile, DeleteTrackedFile::ON_DELETE_IGNORE_REMOTE);

$deleter->execute($trackedFile, DeleteTrackedFile::ON_DELETE_DELETE_IF_NOT_IN_OTHERS);
```
  

## Serving files
This package handles file serving for you based on the config you've given in the `contexts` section and the 
`available_image_templates` section.

However you may serve the files the way you are satisfied with.
For instance if only admins are allowed to view some doc files you can create a route for that
check that the current user is admin or not, then retrieve the file id from the request parameters and
serve it to the user:
```php
<?php

use Reshadman\FileSecretary\Application\EloquentPersistedFile;
use Reshadman\FileSecretary\Infrastructure\FileSecretaryManager;

class SomeController extends \Illuminate\Routing\Controller {
    
    public function getDocument($fileId, FileSecretaryManager $fManager)
    {
        if (!auth()->user()->is_admin) {
            abort(403);
        }
        
        /** @var EloquentPersistedFile $trackedFile */
        $trackedFile = EloquentPersistedFile::findOrFail($fileId);
        
        $diskDriver = $fManager->getContextDriver($trackedFile->getFileableContext());
        
        $path = $trackedFile->full_relative_path;
        
        $contents = $diskDriver->get($path);
        
        if ($contents === false) {
            abort(404);
        }
        
        $headers = ['Content-Type' => $diskDriver->mimeType($path)];
        
        return response($contents, 200, $headers);
    }
    
}

```

If you provide a `driver_base_address` for contexts, the URLs will be generated with prepended base_address
So if your files are public you can simply define the `driver_base_address` of your disk, and then
they are served directly from the disk HTTP endpoint.
 

### Default routes 
 
There is a predefined route which file-secretary provides:

```php
<?php

route('file-secretary.get.download_file', [
    'context_name' => '',
    'context_folder' => '',
    'after_context_path'
]);
```

This route handles file serving for all contexts except the `asset` contexts.

If you don't want this routes to be include in your package, disable it with setting its key to `false` in config.
```php
<?php return [
    // Other config elements
    'load_routes' => false  
];
```

There is a default controller which you can use to attach it to your desired routes:
```php
<?php

\Reshadman\FileSecretary\Presentation\Http\Actions\DownloadFileAction::class;
```

There is a middleware included in the package which fetches the needed data to retrieve files from the cloud
if you are using your own routes with the default controller, this middleware should be wrapped around
the route:

```php
<?php

\Reshadman\FileSecretary\Presentation\Http\Middleware\PrivacyCheckMiddleware::class;
```

### Restricting access
You can decide that the given user is allowed to view the requested file or not, with defining privacy classes
in the config, read the config file as the documentation. 

The file identifiers are passed to your privacy class and then you can decide that the user is allowed to
get this file or not.

All of the privacy classes should implement the following interface:
```php
<?php

\Reshadman\FileSecretary\Application\Privacy\PrivacyInterface::class;
```

> The restriction works only if you use the built-in controller class for serving.

### Serving public files and manipulated images
If you have public contexts, you can use the `driver_base_address` functionality to remove the participcation of PHP

for `basic_file` context category, the base address will be prepended to the relative path, and it will be served 
directly.

### Serving images without the participation of PHP

for `image` context category, the main image is always served from the base address, however the image templates
are still served from PHP, you can create an Nginx reverse proxy which does one of the following:

 - Set `store_manipulated` to `true`, Create an Nginx directive that will call the laravel endpoint
 on file not found of the base address, the laravel endpoint will store the file beside the main so the in the next call
 the image is served from your upstream base address.

 - Set `store_manipulated` to `false` Use a reverse proxy with caching, which calls the laravel endpoint,
 after a successful call  Nginx will cache the image, so the image will be served from Nginx's cache.
 
 - Set `store_manipulated` to another context which uses a local disk, set the disk path as root for Nginx and 
 on 404,  call the laravel endpoint, or make the route path and disk path look the same, So it will be routed
 to the generator controller automatically when the file does not exist.
 
 >Nginx Directives will be added to the package soon. To make it much more simpler.
 
## Helper functions
Will be added soon.
 
## Nginx Directives And Production Notes
Will be added soon.

## Running the Integration Tests
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

## Package Roadmap
 1. Writing more integration tests + isolated object unit tests.
 2. Use more semantic names for features, class names and methods names.
 3. Make the tracking, eloquent independent.
 4. Refactor the code both for design and performance.
 5. In a new release, use a polymorphic model for database tracked files which allows
 to indicate that whether a file has been used somewhere in the other models or not (by design).
  Which in result we can delete unused tracked files. This also works only with SQL databases.
 6. Adding Nginx Directives
 7. Delegate some works to worker queue. 

## About the package
This package has been extracted from [*jobinja.ir - The leading job board and career platform in Iran*](https://jobinja.ir),
This is part of the work for making [jobinja.ir](https://jobinja.ir), [12factor.net](http://12factor.net) compatible.

## License
The MIT License (MIT). Please see License File for more information.
