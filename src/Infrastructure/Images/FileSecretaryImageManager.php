<?php

namespace Jobinja\Services\ImageGenerator;

use Intervention\Image\Image;
use Intervention\Image\ImageManager as InterventionImageManager;
use Reshadman\FileSecretary\Infrastructure\Images\ImageMutateRequest;
use Reshadman\FileSecretary\Infrastructure\Images\TemplateManager;

class FileSecretaryImageManager
{
    /**
     * Available encodings
     *
     * @var array
     */
    public static $availableEncodings = ['jpg', 'png', 'tif', 'bmp', 'data-uri', 'gif'];

    /**
     * @var \Intervention\Image\ImageManager
     */
    protected $manager;

    /**
     * @var TemplateManager
     */
    protected $templateManager;

    /**
     * ImageManager constructor.
     *
     * @param \Intervention\Image\ImageManager $imageManager
     * @param TemplateManager $templateManager
     */
    public function __construct(
        InterventionImageManager $imageManager,
        TemplateManager $templateManager
    ) {
        $this->manager = $imageManager;
        $this->templateManager = $templateManager;
    }

    /**
     * Mutate the image based on the need of the given request
     *
     * @param ImageMutateRequest $request
     * @return Image
     */
    public function mutate(ImageMutateRequest $request)
    {
        return $request->image();
    }

    /**
     * Get mime for image
     *
     * @param \Intervention\Image\Image $image
     * @return string
     */
    public static function getExtensionForImage(Image $image)
    {
        $mimeHash = [
            'image/jpg' => 'jpg',
            'image/jpeg' => 'jpg',
            'image/png' => 'png'
        ];

        return $mimeHash[$image->mime()];
    }
}