<?php

namespace Reshadman\FileSecretary\Application\Usecases;

use Reshadman\FileSecretary\Infrastructure\EloquentPersistedFile;

class StoreTrackedFile
{
    /**
     * @var StoreFile
     */
    private $storeFile;

    public function __construct(StoreFile $storeFile)
    {
        $this->storeFile = $storeFile;
    }

    public function execute(PresentedFile $presentedFile)
    {
        $this->storeFile->execute($presentedFile);

        return EloquentPersistedFile::create([
            'uuid' => $presentedFile->getUuid(),
            'context' => $presentedFile->getContext(),
            'original_name' => $presentedFile->getOriginalName(),
            'file_name' => $presentedFile->getFileName(),
            'sibling_folder' => $presentedFile->getSiblingFolder(),
            'context_folder' => $presentedFile->getContextFolder(),
            'file_hash' => $presentedFile->getMd5Hash(),
            'file_ensured_hash' => $presentedFile->getSha1Hash(),
            'category' => $presentedFile->getCategory()
        ]);
    }
}