<?php

namespace FileSecretaryTests\Overrides;

use Illuminate\Contracts\Container\Container as ContainerContract;
use Illuminate\Foundation\Application as BaseApplication;

class Application extends BaseApplication implements ContainerContract
{
    public function __construct($basePath = null)
    {
        parent::__construct($basePath);
        $this->environmentPath = __DIR__ . '/../../fixtures';

        \Dotenv::load($this->environmentPath(), '.env');
    }
}