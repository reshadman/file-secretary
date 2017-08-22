<?php

namespace Reshadman\FileSecretary\Infrastructure\Images\Templates;

use Intervention\Image\Image;
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

    public function finalize(Image $image)
    {
        return $image->isEncoded() ? $image->getEncoded() : $image->encode();
    }
}