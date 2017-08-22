<?php

namespace Reshadman\FileSecretary\Presentation\Http\Actions;

use Illuminate\Routing\Controller;
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

    public function action($context, $siblingFolder, $fileName, $fileExtension)
    {
        $driver = $this->secretaryManager->getContextDriver($context);

        $config = $this->secretaryManager->getConfig('contexts.' . $context);

        $filePath = $this->secretaryManager->getContextStartingPath($context) . '/' . $siblingFolder . '/' . $fileName . '/' . $fileExtension;


    }
}