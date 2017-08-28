<?php

namespace Reshadman\FileSecretary\Application\Usecases;

use Reshadman\FileSecretary\Application\EloquentPersistedFile;
use Reshadman\FileSecretary\Application\PersistableFile;
use Reshadman\FileSecretary\Application\PresentedFile;
use Reshadman\FileSecretary\Infrastructure\FileSecretaryManager;

class StoreTrackedFile
{
    /**
     * @var StoreFile
     */
    private $storeFile;
    /**
     * @var FileSecretaryManager
     */
    private $fManager;

    public function __construct(StoreFile $storeFile, FileSecretaryManager $fManager)
    {
        $this->storeFile = $storeFile;
        $this->fManager = $fManager;
    }

    /**
     * @param PresentedFile $presentedFile
     * @return PersistableFile|EloquentPersistedFile
     */
    public function execute(PresentedFile $presentedFile)
    {
        $this->storeFile->execute($presentedFile);

        return $this->fManager->getPersistModel()->create([
            'uuid' => $presentedFile->getUuid(),
            'context' => $presentedFile->getContext(),
            'original_name' => $presentedFile->getOriginalName(),
            'file_name' => $presentedFile->getFileName(),
            'sibling_folder' => $presentedFile->getSiblingFolder(),
            'context_folder' => $presentedFile->getContextFolder(),
            'file_hash' => $presentedFile->getMd5Hash(),
            'extension' => $presentedFile->getFileExtension(),
            'file_ensured_hash' => $presentedFile->getSha1Hash(),
            'category' => $presentedFile->getCategory()
        ]);
    }
}