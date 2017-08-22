<?php

namespace FileSecretaryTests;


use Reshadman\FileSecretary\Domain\FileSecretaryManager;

class UploadAssetsTest extends BaseTestCase
{
    public function testArgumentPresence()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->artisan('file-secretary:upload-assets');
    }

    public function testFunctionality()
    {
        $this->artisan('file-secretary:upload-assets', ['--tags' => 'asset_1,asset_2']);

        /** @var FileSecretaryManager $secretaryManager */
        $secretaryManager = app(FileSecretaryManager::class);

        $driver = $secretaryManager->getContextDriver($context = $secretaryManager->getConfig('asset_folders.asset_1')['context']);

        $filePath = $secretaryManager->getAssetStartingPath($context, 'asset_1') . '/' .env('ASSET_1_ID') .  '/buy.csv';

        $contents = $driver->get($filePath);

        $this->assertContains('reza', $contents);
    }
}