<?php

namespace Reshadman\FileSecretary\Infrastructure\Images;

class MadeImageResponse
{
    private $image;
    private $extension;

    public function __construct($image, $extension)
    {
        $this->image = $image;
        $this->extension = $extension;
    }

    public function extension()
    {
        return $this->extension;
    }

    public function image()
    {
        return $this->image;
    }
}