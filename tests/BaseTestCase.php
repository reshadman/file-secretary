<?php

namespace FileSecretaryTests;

use FileSecretaryTests\Overrides\Application;
use FileSecretaryTests\Overrides\ConsoleKernel;
use FileSecretaryTests\Overrides\LoadConfiguration;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Filesystem\FilesystemManager;
use Orchestra\Testbench\TestCase;
use Reshadman\FileSecretary\Infrastructure\FileSecretaryServiceProvider;

class BaseTestCase extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->deleteAllFiles();
    }

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

    protected function deleteAllFiles()
    {
        /** @var FilesystemManager $fileManager */
        $fileManager = app(FilesystemManager::class);

        foreach (config('filesystems.disks') as $disk => $diskConfig) {
            $fileManager->disk($disk)->delete($fileManager->disk($disk)->allFiles());
            foreach ($fileManager->disk($disk)->directories('/') as $dir) {
                $fileManager->disk($disk)->deleteDirectory($dir);
            }
        }
    }

    public function tearDown()
    {
        $this->deleteAllFiles();

        parent::tearDown();
    }
}