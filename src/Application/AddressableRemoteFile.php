<?php

namespace Reshadman\FileSecretary\Application;

use Reshadman\FileSecretary\Infrastructure\UrlGenerator;

class AddressableRemoteFile
{
    private $context;
    private $relative;

    public function __construct($contextData, $relative)
    {
        $this->context = $contextData;
        $this->relative = $relative;
    }

    public static function buildFromArray(array $array)
    {
    }

    public function fullUrl()
    {
        return $this->toUrl();
    }

    public function getContextBaseAddress()
    {
        $base = trim(array_get($this->context, 'driver_base_address', ''), '/');

        return $base;
    }

    public function fullRelative()
    {
        return trim($this->getContextFolder() . '/' . $this->relative, '/');
    }

    public function relative()
    {
        return $this->relative;
    }

    public function getContextFolder()
    {
        return array_get($this->context, 'context_folder');
    }

    public function getContextName()
    {
        return array_get($this->context, 'name');
    }

    public function toUrl()
    {
        return UrlGenerator::fromAddressableRemoteFile($this);
    }

    public function getImageTemplates()
    {
        return UrlGenerator::getImageTemplatesForRemoteFile($this);
    }
}