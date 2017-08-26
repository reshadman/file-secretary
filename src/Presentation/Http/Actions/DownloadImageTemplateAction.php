<?php

namespace Reshadman\FileSecretary\Presentation\Http\Actions;

use Illuminate\Routing\Controller;
use Reshadman\FileSecretary\Application\Usecases\PresentedFile;
use Reshadman\FileSecretary\Domain\FileSecretaryManager;

class DownloadImageTemplateAction extends Controller
{
    /**
     * @var FileSecretaryManager
     */
    private $secretaryManager;

    public function __construct(FileSecretaryManager $secretaryManager)
    {
        $this->secretaryManager = $secretaryManager;
    }

    public function action($context, $siblingFolder, $fileName, $fileExtension = null)
    {
        $driver = $this->secretaryManager->getContextDriver($context);

        if ($fileExtension !== null) {
            $fileExtension = '/' . $fileExtension;
        }

        $filePath = ($fullSibling = $this->secretaryManager->getContextStartingPath($context) . '/' . $siblingFolder . '/') . $fileName . $fileExtension;

        $path = $filePath;
        if ( ! $driver->exists($filePath)) {
            if ($fileName === PresentedFile::MAIN_IMAGE_NAME) {
                abort(404, "Given image not found.");
            } else {
                if ( ! $driver->exists($fullMain = $fullSibling . '/' . PresentedFile::MAIN_IMAGE_NAME . $fileExtension)) {
                    abort(404, "There is no main image for this template.");
                } else {
                    $image = $driver->get($fullMain);
                    $path = $fullMain;
                }
            }
        } else {
            $image = $driver->get($filePath);
        }

        $mimeType = $driver->mimeType($path);

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
}