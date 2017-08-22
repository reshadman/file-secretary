<?php

namespace Reshadman\FileSecretary\Infrastructure\Images;

use Intervention\Image\Image;

class ImageMutateRequest
{
    /**
     * @var Image
     */
    private $image;

    /**
     * ImageMutateRequest constructor.
     * @param Image $image
     */
    public function __construct(Image $image)
    {
        $this->image = $image;
    }

    /**
     * @return Image
     */
    public function image()
    {
        return $this->image;
    }
}