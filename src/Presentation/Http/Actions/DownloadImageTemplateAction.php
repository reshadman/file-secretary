<?php

namespace Reshadman\FileSecretary\Presentation\Http\Actions;

use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Reshadman\FileSecretary\Application\PresentedFile;
use Reshadman\FileSecretary\Application\Usecases\MakeAndStoreImage;
use Reshadman\FileSecretary\Infrastructure\FileSecretaryManager;

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

    /**
     * Action.
     *
     * @param $context
     * @param $fileUuid
     * @param $fileName
     * @param $fileExtension
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function action($context, $fileUuid, $fileName, $fileExtension)
    {
        // If given addresses are for a main file we will simply return that.
        // Else we will try to generate the new image.

        if ($fileName === PresentedFile::MAIN_IMAGE_NAME) {
            return $this->actionMain($context, $fileUuid . '/' . $fileName, $fileExtension);
        } else {
            return $this->actionTemplate($context, $fileUuid, $fileName, $fileExtension);
        }
    }

    /**
     * Action for main file.
     *
     * @param $context
     * @param $path
     * @param $extension
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function actionMain($context, $path, $extension)
    {
        $driver = $this->secretaryManager->getContextDriver($context);
        $path = $this->secretaryManager->getContextStartingPath($context) . '/' . $path;

        $contents = $driver->get($path . '.' . $extension);

        if ($contents === false) {
            abort(404);
        }

        // Simply retrieve the content type from given image name.
        $headers = [
            'Content-Type' => $this->secretaryManager->getMimeForExtension($extension)
        ];

        return response($contents, 200, $headers);
    }

    /**
     * Action for template which will generate the new image and upload it to the proper driver.
     *
     * @param $context
     * @param $siblingFolder
     * @param $template
     * @param $extension
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function actionTemplate($context, $siblingFolder, $template, $extension)
    {
        $context = $this->secretaryManager->getContextDriver($context);
        $starting = $this->secretaryManager->getContextStartingPath($context) . '/' . $siblingFolder;
        $path = $starting . '/' . $template . '.' . $extension;

        $driver = $this->secretaryManager->getContextDriver($context);

        // Simply if we have already generated the file we will download it.
        if ($driver->exists($path)) {
            return response($driver->get($path), 200, [
                'Content-Type' => $this->secretaryManager->getMimeForExtension($extension)
            ]);
        }

        // We are gonna find the main file by iterating through the available
        // files in the sibling folder.
        $mainPath = null;
        foreach ($driver->files($starting) as $filePath) {
            $basename = basename($filePath);

            if (Str::startsWith($basename, [PresentedFile::MAIN_IMAGE_NAME])) {
                $mainPath = $filePath;
                break;
            }
        }

        // If file not found.
        if ($mainPath === null) {
            abort(404);
        }

        try {
            $response = $this->makeImage->execute(
                $context,
                $siblingFolder,
                $driver->get($mainPath),
                $template,
                $extension
            );
            return response($response->getMadeImageResponse()->image(), 200, [
                'Content-Type' => $this->secretaryManager->getMimeForExtension(
                    $response->getMadeImageResponse()->extension()
                )
            ]);
        } catch (\InvalidArgumentException $e) {
            return abort(404);
        }
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