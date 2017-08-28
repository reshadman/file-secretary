<?php

namespace FileSecretaryTests\Overrides;

use Illuminate\Contracts\Foundation\Application;
use Orchestra\Testbench\Bootstrap\LoadConfiguration as BaseLoader;
use Symfony\Component\Finder\Finder;

class LoadConfiguration extends BaseLoader
{
    /**
     * Get all of the configuration files for the application.
     *
     * @param  \Illuminate\Contracts\Foundation\Application $app
     *
     * @return array
     */
    protected function getConfigurationFiles(Application $app)
    {
        $files = [];

        $paths = [
            realpath(__DIR__ . '/../../vendor/orchestra/testbench/fixture/config'),
            (__DIR__ . '/../../fixtures/config')
        ];

        foreach ($paths as $path) {
            foreach (Finder::create()->files()->name('*.php')->in($path) as $file) {
                $files[basename($file->getRealPath(), '.php')] = $file->getRealPath();
            }
        }

        return $files;
    }
}