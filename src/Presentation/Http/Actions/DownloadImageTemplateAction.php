<?php

namespace Reshadman\FileSecretary\Presentation\Http\Actions;

use Illuminate\Routing\Controller;
use Reshadman\FileSecretary\Application\Usecases\MakeAndStoreImage;
use Reshadman\FileSecretary\Application\Usecases\PresentedFile;
use Reshadman\FileSecretary\Domain\FileSecretaryManager;

class DownloadImageTemplateAction extends Controller
{
    /**
     * @var FileSecretaryManager
     */
    private $secretaryManager;
    /**
     * @var MakeAndStoreImage
     */
    private $makeImage;

    public function __construct(FileSecretaryManager $secretaryManager, MakeAndStoreImage $makeImage)
    {
        $this->secretaryManager = $secretaryManager;
        $this->makeImage = $makeImage;
    }

    public function action($context, $siblingFolder, $fileName, $fileExtension = null)
    {
        $driver = $this->secretaryManager->getContextDriver($context);

        $fileExtension = $this->decorateFileExtension($fileExtension);

        $filePath = (
            $fullSibling = $this->getStartingPath($context) . '/' . $siblingFolder . '/'
        ) . $fileName . $fileExtension;

        $path = $filePath;
        $mimeType = null;
        if ( ! $driver->exists($filePath)) {
            if ($fileName === PresentedFile::MAIN_IMAGE_NAME) {
                abort(404, "Given image not found.");
            } else {
                if ( ! $driver->exists($fullMain = $fullSibling . '/' . PresentedFile::MAIN_IMAGE_NAME . $fileExtension)) {
                    abort(404, "There is no main image for this template.");
                } else {
                    $image = $driver->get($fullMain);
                    $image = $this->makeImage->execute($context, $image, $fileName, $fileExtension)->getMadeImageResponse()->image();
                    $path = $fullMain;
                }
            }
        } else {
            $image = $driver->get($filePath);
        }

        $mimeType = 'mimeType';

        $headers = [];

        if ($mimeType !== null) {
            $headers['Content-type'] = $mimeType;
        }

        $headers = [
            'Content-Disposition' => 'attachment; filename=' . $fileName,
        ];

        $contents = '';

        return response()->make($contents, 200, $headers);
    }

    /**
     * @param $fileExtension
     * @return string
     */
    protected function decorateFileExtension($fileExtension): string
    {
        if ($fileExtension !== null) {
            $fileExtension = '.' . $fileExtension;
        }
        return $fileExtension;
    }

    /**
     * @param $context
     * @return array|mixed
     */
    protected function getStartingPath($context)
    {
        return $this->secretaryManager->getContextStartingPath($context);
    }
}