<?php

namespace Reshadman\FileSecretary\Application\Usecases;

use Reshadman\FileSecretary\Application\PersistableFile;
use Reshadman\FileSecretary\Infrastructure\FileSecretaryManager;

class DeleteTrackedFile
{
    const ON_DELETE_IGNORE_REMOTE = 0;
    const ON_DELETE_DELETE_REMOTE = 1;
    const ON_DELETE_DELETE_IF_NOT_IN_OTHERS = 2;

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
     * @param $onDelete
     */
    public function execute($fileUuidOrInstance, $onDelete = DeleteTrackedFile::ON_DELETE_DELETE_REMOTE)
    {
        if (!is_object($fileUuidOrInstance)) {
            $model = $this->fManager->getPersistModel();
            $fileUuidOrInstance = $model->where('uuid', $fileUuidOrInstance)->firstOrFail();
        }

        $delete = function () use ($fileUuidOrInstance) {
            $this->deleteFile->execute($fileUuidOrInstance->getFileableContext(), $fileUuidOrInstance->getFinalPath());
        };

        switch ($onDelete) {
            case static::ON_DELETE_DELETE_REMOTE:
                $delete();
                break;

            case static::ON_DELETE_DELETE_IF_NOT_IN_OTHERS:
                $exists = $this->fManager->getPersistModel()
                    ->where('id', '!=', $fileUuidOrInstance->getFileableIdentifier())
                    ->where('uuid', '=', $fileUuidOrInstance->getFileableUuid())
                    ->exists();

                if (!$exists) {
                    $delete();
                }
                break;
        }

        $fileUuidOrInstance->delete();
    }
}