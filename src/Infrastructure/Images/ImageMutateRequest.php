<?php

namespace Reshadman\FileSecretary\Infrastructure\Images;

use Intervention\Image\Image;
use Intervention\Image\ImageManager;

class ImageMutateRequest
{
    /**
     * @var Image
     */
    private $image;

    private $template;

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

    public static function fromImageContent($image)
    {
        /** @var ImageManager $im */
        $im = app(ImageManager::class);

        $image = $im->make($image);

        return new self($image);
    }

    public function forTemplate($template)
    {
        $this->template = $template;
        return $this;
    }

    public function assert()
    {
        if ($this->template === null) {
            throw new \InvalidArgumentException("Template not given.");
        }
    }

    public function template()
    {
        return $this->template();
    }
}