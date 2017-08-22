<?php

namespace Reshadman\FileSecretary\Presentation\Http\Actions;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Routing\Controller;
use Reshadman\FileSecretary\Domain\FileSecretaryManager;

class DownloadBasicFileAction extends Controller
{
    /**
     * @var FileSecretaryManager
     */
    private $secretaryManager;

    public function __construct(FileSecretaryManager $secretaryManager)
    {
        $this->secretaryManager = $secretaryManager;
    }

    public function action($context, $uuid, $extension)
    {
        /** @var FilesystemAdapter $driver */
        $driver = $this->secretaryManager->getContextDriver($context);

        $path = $this->secretaryManager->getContextStartingPath($context) . '/' . ($fileName = $uuid . '.' . $extension);

        $mimeType = $driver->mimeType($path);

        $headers = [
            'Content-type' => $mimeType,
            'Content-Disposition' => 'attachment; filename='. $fileName,
        ];

        $contents = $driver->get($path);

        return response()->make($contents, 200, $headers);
    }
}