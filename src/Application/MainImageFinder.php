<?php

namespace Reshadman\FileSecretary\Application;

use Illuminate\Filesystem\FilesystemAdapter;
use Reshadman\FileSecretary\Infrastructure\FileSecretaryManager;

class MainImageFinder
{
    /**
     * @var FileSecretaryManager
     */
    private $secretaryManager;

    /**
     * MainImageFinder constructor.
     * @param FileSecretaryManager $secretaryManager
     */
    public function __construct(FileSecretaryManager $secretaryManager)
    {
        $this->secretaryManager = $secretaryManager;
    }

    /**
     * Find the main image path.
     *
     * @param $driver
     * @param $path
     * @return string|null
     */
    public function find(FilesystemAdapter $driver, $path)
    {
        // We loop through all file paths in the sibling folder until we see the
        // main image if the main image is found
        // We will retrieve its contents and pass it to the image
        // mutator command.
        // If not we will throw a not found exception.
        $image = null;
        foreach ($driver->files($path) as $filePath) {
            $fileName = basename($filePath);
            $fileName = explode('.', $fileName);

            // If the file name has dot we will consider the last dot as extension.
            // And we will remove it otherwise it is the file name without extension.
            if (count($fileName) > 1) {
                array_pop($fileName);
                $fileName = implode('.', $fileName);
            } else {
                $fileName = $fileName[0];
                $extension = null;
            }

            if ($fileName === PresentedFile::MAIN_IMAGE_NAME) {
                return $filePath;
            }
        }

        return null;
    }
}