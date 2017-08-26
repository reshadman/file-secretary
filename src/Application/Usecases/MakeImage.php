<?php

namespace Reshadman\FileSecretary\Application\Usecases;

use Jobinja\Services\ImageGenerator\FileSecretaryImageManager;
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

    public function execute($imageable, $template)
    {
        $request = ImageMutateRequest::fromImageContent($imageable)->forTemplate($template);

        return $this->fImageManager->mutate($request);
    }
}