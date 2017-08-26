<?php

namespace Reshadman\FileSecretary\Infrastructure\Images;

use Intervention\Image\Image;

interface TemplateInterface
{
    public function makeFromImage(Image $image);

    public function finalize(Image $image, $wantedFormat);
}