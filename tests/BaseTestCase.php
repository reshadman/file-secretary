<?php

namespace FileSecretaryTests;

use FileSecretaryTests\Overrides\Application;
use FileSecretaryTests\Overrides\ConsoleKernel;
use FileSecretaryTests\Overrides\LoadConfiguration;
use Illuminate\Contracts\Console\Kernel;
use Orchestra\Testbench\TestCase;
use Reshadman\FileSecretary\Infrastructure\FileSecretaryServiceProvider;

class BaseTestCase extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [FileSecretaryServiceProvider::class];
    }

    protected function resolveApplicationConsoleKernel($app)
    {
        $app->singleton(Kernel::class, ConsoleKernel::class);
    }

    /**
     * Resolve application implementation.
     *
     * @return \Illuminate\Foundation\Application
     */
    protected function resolveApplication()
    {
        $app = new Application($this->getBasePath());

        $app->bind('Illuminate\Foundation\Bootstrap\LoadConfiguration', LoadConfiguration::class);

        return $app;
    }
}