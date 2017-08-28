<?php

namespace FileSecretaryTests;

use Reshadman\FileSecretary\Application\PresentedFile;
use Reshadman\FileSecretary\Application\Usecases\DeleteFile;
use Reshadman\FileSecretary\Application\Usecases\StoreFile;
use Reshadman\FileSecretary\Infrastructure\FileSecretaryManager;

class DeleteFileTest extends BaseTestCase
{
    public function testDeleteWorks()
    {
        $file = __DIR__ . '/../stub/logo.jpg';

        $presented = new PresentedFile('file_manager_private', $file, PresentedFile::FILE_TYPE_PATH);

        /** @var StoreFile $store */
        $store = app(StoreFile::class);

        $addressable = $store->execute($presented);

        /** @var DeleteFile $delete */

        $this->assertTrue($presented->getContextDriver()->exists($addressable->fullRelative()));

        $delete = app(DeleteFile::class);

        $delete->execute('file_manager_private', $addressable->fullRelative());

        $this->assertFalse($presented->getContextDriver()->exists($addressable->fullRelative()));
    }

    public function testDeleteDoesNotDeleteOtherFiles()
    {
        /** @var FileSecretaryManager $manager */
        $manager = app(FileSecretaryManager::class);
        $context = 'file_manager_private';
        $creator = function ($file) {
            $presented = new PresentedFile('file_manager_private', $file, PresentedFile::FILE_TYPE_PATH);

            /** @var StoreFile $store */
            $store = app(StoreFile::class);

            return $store->execute($presented);
        };

        $addressable1 = $creator($file1 = __DIR__ . '/../stub/logo.jpg');
        $addressable2 = $creator($file2 = __DIR__ . '/../stub/asset_tags/asset_1/buy.csv');

        $this->assertTrue($manager->getContextDriver($context)->exists($addressable1->fullRelative()));
        $this->assertTrue($manager->getContextDriver($context)->exists($addressable2->fullRelative()));

        $delete = app(DeleteFile::class);

        $delete->execute('file_manager_private', $addressable1->fullRelative());

        $this->assertFalse($manager->getContextDriver($context)->exists($addressable1->fullRelative()));
        $this->assertTrue($manager->getContextDriver($context)->exists($addressable2->fullRelative()));
    }
}