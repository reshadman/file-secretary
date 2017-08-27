## Running the Integration Tests
There are integration tests written for this package. To run integration
tests do as the following:

 1. Create your `phpunit.xml` file based on the packages's `phpunit.dist.xml`:
 `cp phpunit.dist.xml phpunit.xml`
 2. Fill the phpunit config with your environment variables.
 The package has been tested with **Rackspace** Object storage, to prove the 
 functionality in cloud. You can change the `phpunit.xml` file and the configs in `fixtures/config/`
 to integrate them with your testing environment.
 3. Run the tests with `vendor/bin/phpunit --debug`
 
> Currently there is no isolated object unit testing for this package. 
> They will be added in next releases.

## About the package
This package has been extracted from [*jobinja.ir* - The leading job board and career platform in Iran](https://jobinja.ir),
This is part of the work for making [jobinja.ir](https://jobinja.ir), [12factor.net](http://12factor.net) compatible.

## License

The MIT License (MIT). Please see License File for more information.
