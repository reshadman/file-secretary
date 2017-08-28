# Laravel File Secretary
Get rid of anything related to files in Laravel, This package handles all for you.

## What does this package do?
 1. **Handles your public assets** (.css, .js, .etc) to be served through your 
 CDN provider.
 Unlike other solutions 
 there is no runtime i/o needed for retrieving the unique id needed for 
 cache purging on deploys.
 
 2. **Handles all the image resizing needs** with simple configuration, 
 Images are generated on the fly
 for once, and are stored in your CDN provider, 
 They could be served without the participation of PHP
 all handled with a simple *nginx snippet* included in the package. 
 3. **Detects redundant files**, File names are generated based on the 
 filesize + a hash function.
 so redundant files could not exist technically, 
 You can implement your own file name generator, too.
 4. **Handles basic files** with a simple method call. 
 They can be served without the participation of PHP. and can they can
 be addressed with the package's functions if they are public.
 5. **Allows Database Tracking** (Optional), 
 you can use the eloquent model to relate files to your other models, easily.
 You can also implement your own eloquent model for more flexibility.
 6. **Simple functions** for dealing with resizable image urls, file urls, 
 asset urls etc.
 7. **A Simple controller for serving private/public files** can be used to 
 serve both resizable images, and basic files.
 You can implement your own access control for serving them based on config.
 
### Installation
```bash
composer require reshadman/file-secretary 1.*
```

### Publish config and migrations
To publish configuration:
```bash
php artisqan vendor:publish \
    --provider=Reshadman\FileSecretary\Infrastructure\FileSecretaryServiceProvider \
    --tag=config
```

> By default the migration for database tracking is also published, you delete it if you don't want the functionality.

To publish migrations:
```bash
php artisqan vendor:publish \
    --provider=Reshadman\FileSecretary\Infrastructure\FileSecretaryServiceProvider \
    --tag=migrations
```

### Configuration
For understanding how this package works please read the documentation
blocks in the default config file here:

[config/file_secretary.php](https://github.com/reshadman/file-secretary/blob/master/fixtures/config/file_secretary.php)


### Usage
The best way to see the usage is by reading the integration tests.

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
