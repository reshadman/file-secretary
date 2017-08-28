<?php

namespace Reshadman\FileSecretary\Application\Usecases;

use Reshadman\FileSecretary\Application\PresentedFile;
use Reshadman\FileSecretary\Infrastructure\FileSecretaryManager;

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
    private $fManager;

    public function __construct(MakeImage $makeImage, StoreFile $storeFile, FileSecretaryManager $fManager)
    {
        $this->storeFile = $storeFile;
        $this->makeImage = $makeImage;
        $this->fManager = $fManager;
    }

    /**
     * Execute the command.
     *
     * @param $context
     * @param $uuid
     * @param $imageable
     * @param $template
     * @param null $extension
     * @return MakeAndStoreImageResponse
     */
    public function execute($context, $uuid, $imageable, $template, $extension)
    {
        // Create the template.
        $response = $this->makeImage->execute($imageable, $template, $extension);

        $storeManipulated = $this->fManager->getConfig("contexts.{$context}.store_manipulated", true);

        if ($storeManipulated) {

            // Store it in the cloud.
            $storeResponse = $this->storeFile->execute(new PresentedFile(
                $storeManipulated,
                $response->image(),
                PresentedFile::FILE_TYPE_CONTENT,
                null,
                [
                    'image_template_name' => $template . '.' . $response->extension(),
                    'uuid' => $uuid // Causes to not to create another folder.
                ]
            ));
        } else {
            $storeResponse = null;
        }

        return new MakeAndStoreImageResponse($response, $storeResponse);
    }
}