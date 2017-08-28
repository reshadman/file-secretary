<?php

namespace Reshadman\FileSecretary\Infrastructure;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;
use Reshadman\FileSecretary\Infrastructure\Images\TemplateManager;

class FileSecretaryServiceProvider extends ServiceProvider
{
    const PRODUCTION_ENV_NAME = 'production';

    /**
     * Boot.
     *
     * @param Dispatcher $dispatcher
     */
    public function boot(Dispatcher $dispatcher)
    {
        if ($this->app->environment() !== static::PRODUCTION_ENV_NAME) {
            $this->publishes([
                __DIR__ . '/../../fixtures/config/file_secretary.php' => config_path('file_secretary.php')
            ], 'config');

            $this->publishes([
                __DIR__ . '/../../fixtures/migrations'
            ], 'migrations');
        }

        foreach ($this->app['config']->get('file_secretary.listen', []) as $event => $listeners) {
            foreach ($listeners as $listener) {
                $dispatcher->listen($event, $listener);
            }
        }

        if ( ! $this->app->routesAreCached() && $this->app['config']->get('file_secretary.load_routes', false)) {
            require __DIR__ . '/../Presentation/Http/routes.php';
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

        $this->app->singleton(FileSecretaryManager::class, function ($app) use ($config) {

            return new FileSecretaryManager($config, $app['filesystem']);

        });

        $this->app->singleton(MimeDbRepository::class, function () {
            return new MimeDbRepository();
        });

        $this->app->singleton(TemplateManager::class, function ($app) {
            $templates = $app['config']->get('file_secretary.available_image_templates', []);
            return new TemplateManager($templates);
        });
    }
}