<?php

namespace FileSecretaryTests;

use Reshadman\FileSecretary\Application\PresentedFile;
use Reshadman\FileSecretary\Application\Usecases\StoreFile;

class StoreFileTest extends BaseTestCase
{
    public function testStoreFileForImage()
    {
        $file = __DIR__  . '/../stub/logo.jpg';

        $md5Content = file_get_contents($file);

        $presented = new PresentedFile('images_public', $file, PresentedFile::FILE_TYPE_PATH);

        /** @var StoreFile $store */
        $store = app(StoreFile::class);

        $addressable = $store->execute($presented);

        $remoteMd5 = ($presented->getContextDriver()->get($addressable->fullRelative()));

        $this->assertEquals($remoteMd5, $md5Content);
    }

    public function testStoreFileForBasicFile()
    {
        $file = __DIR__  . '/../stub/logo.jpg';

        $md5Content = file_get_contents($file);

        $presented = new PresentedFile('file_manager_private', $file, PresentedFile::FILE_TYPE_PATH);

        /** @var StoreFile $store */
        $store = app(StoreFile::class);

        $addressable = $store->execute($presented);

        $remoteMd5 = ($presented->getContextDriver()->get($addressable->fullRelative()));

        $this->assertEquals($remoteMd5, $md5Content);
    }
}