<?php

namespace Reshadman\FileSecretary\Application\Usecases;

class MakeAndStoreImage
{
    /**
     * @var StoreFile
     */
    private $storeFile;
    /**
     * @var MakeImage
     */
    private $makeImage;

    public function __construct(MakeImage $makeImage, StoreFile $storeFile)
    {
        $this->storeFile = $storeFile;
        $this->makeImage = $makeImage;
    }

    public function execute($context, $imageable, $template, $extension = null)
    {
        $response = $this->makeImage->execute($imageable, $template);

        $storeResponse = $this->storeFile->execute(new PresentedFile(
            $context,
            $response->image(),
            PresentedFile::FILE_TYPE_CONTENT,
            null,
            [
                'image_template_name' => $template . ($extension ? '.' . $extension : '')
           ]
        ));

        return new MakeAndStoreImageResponse($response, $storeResponse);
    }
}