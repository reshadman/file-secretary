# Laravel File Secretary
Get rid of anything related to files in Laravel, This package handles all for you. Anything we mean.

## What does this package do?
 1. **Handles your public assets** (.css, .js, .etc) to be served through your CDN provider.
 Unlike other solutions 
 there is no runtime i/o needed for retrieving the unique id needed for cache purging on deploys.
 2. **Handles all the image resizing needs** with simple configuration, Images are generated on the fly
 for once, and are stored in your CDN provider, They could be served without the participation of PHP
 all handled with a simple *nginx snippet*. 
 3. **Detects redundant files**, File names are generated based on the filesize + a hash function.
 so redundant files could not exist technically, You can implement your own file name generator, too.
 4. **Handles basic files** with a simple method call. They can be served without the participation of PHP.
 5. **Allows Database Tracking** (Optional), you can use the eloquent model to relate files to your other models, easily.
 You can also implement your own eloquent model for more flexibility.
 6. **Simple functions** for dealing with resizable image urls, file urls, asset urls etc.
 7. **A Simple controller for serving private/public files** can be used to serve both resizable images, and basic files.
 You can implement your own access control for serving based on config.
 
## Running the Integration Tests
There are integration tests written for this package. To run integration
tests do as the following:

 1. Create your `phpunit.xml` file based on the packages's `phpunit.dist.xml`:3
 
 `cp phpunit.dist.xml phpunit.xml`
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

## About the package
This package has been extracted from [*jobinja.ir - The leading job board and career platform in Iran*](https://jobinja.ir),
This is part of the work for making [jobinja.ir](https://jobinja.ir), [12factor.net](http://12factor.net) compatible.

## License

The MIT License (MIT). Please see License File for more information.
