<?php

namespace Reshadman\FileSecretary\Application\Usecases;

use Reshadman\FileSecretary\Infrastructure\Images\FileSecretaryImageManager;
use Reshadman\FileSecretary\Infrastructure\Images\ImageMutateRequest;

class MakeImage
{
    /**
     * @var FileSecretaryImageManager
     */
    private $fImageManager;

    public function __construct(FileSecretaryImageManager $fImageManager)
    {
        $this->fImageManager = $fImageManager;
    }

    public function execute($imageable, $template, $extension)
    {
        $request = ImageMutateRequest::fromImageContent($imageable)->forTemplate($template)->forExtension($extension);

        return $this->fImageManager->mutate($request);
    }
}