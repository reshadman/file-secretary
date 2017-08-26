<?php

namespace Reshadman\FileSecretary\Application\Usecases;

use Reshadman\FileSecretary\Application\AddressableRemoteFile;
use Reshadman\FileSecretary\Application\PresentedFile;

class StoreFile
{
    public function execute(PresentedFile $file)
    {
        $driver = $file->getContextDriver();

        $driver->put($file->getFullDriverPath(), $file->getFileContents());

        return new AddressableRemoteFile($file->getContextData(), $file->getNewPath());
    }
}