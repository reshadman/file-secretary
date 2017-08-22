<?php

namespace Reshadman\FileSecretary\Infrastructure;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;
use Reshadman\FileSecretary\Domain\FileSecretaryManager;

class FileSecretaryServiceProvider extends ServiceProvider
{
    public function boot(Dispatcher $dispatcher)
    {
        $events = $this->app['config']->get('file_secretary.listen', []);

        foreach ($events as $event => $listeners) {
            foreach ($listeners as $listener) {
                $dispatcher->listen($event, $listener);
            }
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $config = $this->app['config']->get('file_secretary');

        $this->app->singleton(FileSecretaryManager::class, function ($app) use($config) {

            return new FileSecretaryManager($config, $app['filesystem']);

        });
    }
}