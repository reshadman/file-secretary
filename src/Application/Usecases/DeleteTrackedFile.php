<?php

namespace Reshadman\FileSecretary\Application\Usecases;

use Reshadman\FileSecretary\Infrastructure\EloquentPersistedFile;

class DeleteTrackedFile
{
    public function __construct(DeleteFile $deleteFile)
    {
        $this->deleteFile = $deleteFile;
    }

    public function execute($fileUuidOrInstance)
    {
        if (!$fileUuidOrInstance instanceof EloquentPersistedFile) {
            $fileUuidOrInstance = EloquentPersistedFile::where('uuid', $fileUuidOrInstance)->firstOrFail();
        }

        $this->deleteFile->execute($fileUuidOrInstance->getFileableContext(), $fileUuidOrInstance->getFinalPath());
        $fileUuidOrInstance->delete();
    }
}