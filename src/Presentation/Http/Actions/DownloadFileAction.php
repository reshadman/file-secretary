<?php

namespace Reshadman\FileSecretary\Presentation\Http\Actions;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Reshadman\FileSecretary\Application\ContextCategoryTypes;
use Reshadman\FileSecretary\Application\MainImageFinder;
use Reshadman\FileSecretary\Application\PresentedFile;
use Reshadman\FileSecretary\Application\PrivacyCheckNeeds;
use Reshadman\FileSecretary\Application\Usecases\MakeAndStoreImage;
use Reshadman\FileSecretary\Infrastructure\FileSecretaryManager;
use Reshadman\FileSecretary\Presentation\Http\Middleware\PrivacyCheckMiddleware;

class DownloadFileAction extends Controller
{
    /**
     * @var FileSecretaryManager
     */
    private $secretaryManager;
    /**
     * @var MakeAndStoreImage
     */
    private $makeImage;
    /**
     * @var Request
     */
    private $request;
    /**
     * @var MainImageFinder
     */
    private $mainImageFinder;

    /**
     * DownloadFileAction constructor.
     * @param Request $request
     * @param FileSecretaryManager $secretaryManager
     * @param MakeAndStoreImage $makeImage
     * @param MainImageFinder $mainImageFinder
     */
    public function __construct(
        Request $request,
        FileSecretaryManager $secretaryManager,
        MakeAndStoreImage $makeImage,
        MainImageFinder $mainImageFinder
    ) {
        $this->secretaryManager = $secretaryManager;
        $this->makeImage = $makeImage;
        $this->request = $request;
        $this->mainImageFinder = $mainImageFinder;
    }

    /**
     * This delegates to other methods.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function action()
    {
        // We retrieve the file spec from the merged instance in the request
        // (Which has been merged by the middleware)
        /** @var PrivacyCheckNeeds $privacyNeeds */
        $privacyNeeds = $this->request->input(PrivacyCheckMiddleware::REQUEST_PARAM_KEY);

        $contextData = $this->secretaryManager->getContextData($privacyNeeds->getContextName());

        switch ($contextData['category']) {
            case ContextCategoryTypes::TYPE_IMAGE:
                return $this->downloadImage($privacyNeeds);
            case ContextCategoryTypes::TYPE_MANIPULATED_IMAGE:
                return $this->downloadTemplate($privacyNeeds);

            case ContextCategoryTypes::TYPE_BASIC_FILE:
                return $this->downloadFile($privacyNeeds);

            default:
                throw new \InvalidArgumentException("The given context category is not supported for download.");
        }
    }

    /**
     * Download the image, if it is a template it will be
     * delegated to the template downloader.
     *
     * @param PrivacyCheckNeeds $needs
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    protected function downloadImage(PrivacyCheckNeeds $needs)
    {
        if ($needs->getFileName() !== PresentedFile::MAIN_IMAGE_NAME) {
            return $this->downloadTemplate($needs);
        }

        $folderStart = $this->secretaryManager->getContextStartingPath($needs->getContextName());
        $driver = $this->secretaryManager->getContextDriver($needs->getContextName());
        $path =  $folderStart . '/' .$needs->getRelativePath();

        $contents = $driver->get($path);

        if ($contents === false) {
            abort(404);
        }

        $headers = [
            'Content-Type' => $this->secretaryManager->getMimeForExtension($needs->getFileExtension())
        ];

        $headers = $this->mutateHeaders($needs, $needs->getContextName(), $headers);

        return response($contents, 200, $headers);
    }

    /**
     * Download the template
     *
     * @param PrivacyCheckNeeds $needs
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    protected function downloadTemplate(PrivacyCheckNeeds $needs)
    {
        $contextData = $this->secretaryManager->getContextData($needs->getContextName());

        // This is equal for both manipulated context and image context
        $startingPath = $needs->getContextFolder() . '/' . $needs->getFileUuid() . '/';

        // If the context has manipulated image category we will find the "main"
        // Image from the parent context.
        if ($contextData['category'] === ContextCategoryTypes::TYPE_MANIPULATED_IMAGE) {
            $parentContext = $this->secretaryManager->getManipulatedImageParentContext($needs->getContextName());
            $searchDriver = $this->secretaryManager->getContextDriver($parentContext);
        } else {
            $searchDriver = $this->secretaryManager->getContextDriver($needs->getContextName());
        }

        $image = $this->mainImageFinder->find($searchDriver, $startingPath);

        if ($image === null) {
            return abort(404);
        } else {
            $image = $searchDriver->get($image);
        }

        $response = $this->makeImage->execute(
            $needs->getContextName(),
            $needs->getFileUuid(),
            $image,
            $needs->getFileUuid(),
            $needs->getFileExtension()
        );

        // Simply retrieve the content type from given image name.
        $headers = [
            'Content-Type' => $this->secretaryManager->getMimeForExtension(
                $response->getMadeImageResponse()->extension()
            )
        ];

        $headers = $this->mutateHeaders($needs, $needs->getContextName(), $headers);

        return response($response->getMadeImageResponse()->image(), 200, $headers);
    }

    /**
     * Download the file
     *
     * @param PrivacyCheckNeeds $needs
     * @return \Illuminate\Http\Response
     */
    protected function downloadFile(PrivacyCheckNeeds $needs)
    {
        /** @var FilesystemAdapter $driver */
        $driver = $this->secretaryManager->getContextDriver($context = $needs->getContextName());

        $path = $this->secretaryManager->getContextStartingPath($context) . '/' . ($needs->getRelativePath());

        if (!$driver->exists($path)) {
            abort(404);
        }

        $mimeType = $driver->mimeType($path);

        $headers = [
            'Content-type' => $mimeType,
        ];

        $headers = $this->mutateHeaders($needs, $context, $headers);

        $contents = $driver->get($path);

        return response()->make($contents, 200, $headers);
    }

    /**
     * @param PrivacyCheckNeeds $needs
     * @param $context
     * @param $headers
     * @return mixed
     */
    protected function mutateHeaders(PrivacyCheckNeeds $needs, $context, $headers)
    {
        $contextData = $this->secretaryManager->getContextData($context);

        // We allow to merge additional headers like force download or etc.
        if (
            array_key_exists('headers_mutator', $contextData) &&
            $contextData['headers_mutator'] instanceof \Closure
        ) {
            $headers = $contextData['headers_mutator']($needs, $headers);
        }
        return $headers;
    }
}