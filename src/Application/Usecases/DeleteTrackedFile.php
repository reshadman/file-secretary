<?php

namespace Reshadman\FileSecretary\Application\Usecases;

use Reshadman\FileSecretary\Application\PersistableFile;
use Reshadman\FileSecretary\Infrastructure\FileSecretaryManager;

class DeleteTrackedFile
{
    /**
     * @var FileSecretaryManager
     */
    private $fManager;

    public function __construct(DeleteFile $deleteFile, FileSecretaryManager $fManager)
    {
        $this->deleteFile = $deleteFile;
        $this->fManager = $fManager;
    }

    /**
     * @param string|PersistableFile $fileUuidOrInstance
     */
    public function execute($fileUuidOrInstance)
    {
        if (!is_a($fileUuidOrInstance, $model = $this->fManager->getPersistModel())) {
            $fileUuidOrInstance = $model->where('uuid', $fileUuidOrInstance)->firstOrFail();
        }

        $this->deleteFile->execute($fileUuidOrInstance->getFileableContext(), $fileUuidOrInstance->getFinalPath());
        $fileUuidOrInstance->delete();
    }
}