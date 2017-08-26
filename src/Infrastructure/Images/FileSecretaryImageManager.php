<?php

namespace Jobinja\Services\ImageGenerator;

use Intervention\Image\ImageManager as InterventionImageManager;
use Reshadman\FileSecretary\Infrastructure\MimeDbRepository;
use Reshadman\FileSecretary\Infrastructure\Images\ImageMutateRequest;
use Reshadman\FileSecretary\Infrastructure\Images\MadeImageResponse;
use Reshadman\FileSecretary\Infrastructure\Images\TemplateManager;

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
        $mimeRepo = app(MimeDbRepository::class);
        $forFirst = $mimeRepo->findType($first);
        $forSecond = $mimeRepo->findType($second);

        return $forFirst === $forSecond;
    }
}