<?php

namespace Reshadman\FileSecretary\Application\Usecases;

class StoreFile
{
    public function execute(PresentedFile $file)
    {
        $driver = $file->getContextDriver();

        $driver->put($file->getFullDriverPath(), $file->getFileContents());

        return new AddressableRemoteFile($file->getContextData(), $file->getNewPath());
    }
}