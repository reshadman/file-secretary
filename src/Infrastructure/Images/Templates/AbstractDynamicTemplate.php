<?php

namespace Reshadman\FileSecretary\Infrastructure\Images\Templates;

use Intervention\Image\Image;
use MimeTyper\Repository\MimeDbRepository;
use Reshadman\FileSecretary\Infrastructure\Images\DynamicTemplateInterface;

abstract class AbstractDynamicTemplate implements DynamicTemplateInterface
{
    protected $args = [];

    public function setArgs(array $args = [])
    {
        $this->args = $args;
        return $this;
    }

    public function getArg($key, $force = false)
    {
        $args = $this->getArgs();

        if ($force && !isset($args[$key])) {
            throw new \LogicException("Arg : {$key} not given.");
        }

        return array_get($args, $key);
    }

    public function getArgs()
    {
        return $this->args;
    }

    public function finalize(Image $image, $wantedFormat = null)
    {
        return $image->isEncoded() ? $image->getEncoded() : $image->encode();
    }

    protected function checkExtension($extension)
    {
        if ($extension === null) {
            return;
        }

        $availableExtensions = $this->getArg('encodings');

        if ($availableExtensions === null) {
            return;
        }

        if (!in_array($extension, $availableExtensions)) {
            throw new \InvalidArgumentException("Given extension is not supported.");
        }
    }
}