<?php

namespace Reshadman\FileSecretary\Application;

class AddressableRemoteFile
{
    private $context;
    private $relative;

    public function __construct($contextData, $relative)
    {
        $this->context = $contextData;
        $this->relative = $relative;
    }

    public function getContextFolder()
    {
        return array_get($this->context, 'context_folder');
    }

    public function fullRelative()
    {
        return trim($this->getContextFolder() . '/' . $this->relative, '/');
    }

    public function fullUrl()
    {
        return $this->getContextBaseAddress() . '/' . $this->fullRelative();
    }

    public function getContextBaseAddress()
    {
        $base = trim(array_get($this->context, 'driver_base_address', ''), '/');

        return $base;
    }

    public function getContextName()
    {
        return array_get($this->context, 'name');
    }
}