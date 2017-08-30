<?php

namespace FileSecretaryTests;


use Illuminate\Support\Str;
use Reshadman\FileSecretary\Infrastructure\FileSecretaryManager;
use Reshadman\FileSecretary\Infrastructure\UrlGenerator;

class UploadAssetsTest extends BaseTestCase
{
    public function setUp()
    {
        parent::setUp();

        @mkdir(public_path('/asset_tags'), 0755);

        copyDirectory(__DIR__ . '/../stub/asset_tags/', public_path('/asset_tags/'));
    }

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

        $filePath = $secretaryManager->getAssetStartingPath($context,
                'asset_1') . '/' . env('ASSET_1_ID') . '/asset_tags/asset_1/csvs/buy.csv';

        $contents = $driver->get($filePath);

        $this->assertContains('reza', $contents);

        $generatedAddressNoForce = UrlGenerator::asset('asset_1', $rel = 'asset_tags/asset_1/csvs/buy.csv');

        $this->assertContains('localhost', $generatedAddressNoForce);

        $generatedWithForce = UrlGenerator::asset('asset_1', $rel = 'asset_tags/asset_1/csvs/buy.csv', true);

        $this->assertTrue(Str::startsWith($generatedWithForce,'https://assets.jobinja.ir/'));

    }

    public function tearDown()
    {
        deleteDirectory(public_path('asset_tags'));
        parent::tearDown();
    }
}

function copyDirectory($source, $dest, $permissions = 0755)
{
    if (is_link($source)) {
        return symlink(readlink($source), $dest);
    }

    if (is_file($source)) {
        return copy($source, $dest);
    }

    if ( ! is_dir($dest)) {
        mkdir($dest, $permissions);
    }

    $dir = dir($source);
    while (false !== $entry = $dir->read()) {
        // Skip pointers
        if ($entry == '.' || $entry == '..') {
            continue;
        }

        // Deep copy directories
        copyDirectory("$source/$entry", "$dest/$entry", $permissions);
    }

    $dir->close();
    return true;
}

function deleteDirectory($target)
{
    if (is_dir($target)) {
        $files = glob($target . '*', GLOB_MARK);

        foreach ($files as $file) {
            @deleteDirectory($file);
        }

        @rmdir($target);
    } elseif (is_file($target)) {
        @unlink($target);
    }
}
