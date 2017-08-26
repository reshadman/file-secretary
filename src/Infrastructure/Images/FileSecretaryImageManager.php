<?php

namespace Reshadman\FileSecretary\Infrastructure\Images;

use Intervention\Image\ImageManager as InterventionImageManager;
use Reshadman\FileSecretary\Infrastructure\MimeDbRepository;

class FileSecretaryImageManager
{
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
     * @return MadeImageResponse
     */
    public function mutate(ImageMutateRequest $request)
    {
        $instance = $this->templateManager->getTemplateInstance($request->template());

        $image = $instance->makeFromImage($request->image());

        $image = $instance->finalize($image, $request->extension());

        return new MadeImageResponse($image, $request->extension());
    }

    public static function extensionsAreEqual($first, $second)
    {
        $first = strtolower($first);
        $second = strtolower($second);

        $mimeRepo = app(MimeDbRepository::class);
        $forFirst = $mimeRepo->findExtension($mimeRepo->findType($first));
        $forSecond = $mimeRepo->findExtension($mimeRepo->findType($second));

        return $forFirst === $forSecond;
    }
}