<?php

namespace FileSecretaryTests;

use Illuminate\Support\Str;
use Reshadman\FileSecretary\Application\PresentedFile;

class PresentedFileTest extends BaseTestCase
{
    private $context = 'file_manager_private';

    public function testUrlFile()
    {
        $contents = file_get_contents($url ="https://www.google.com/robots.txt");

        $hash = md5($contents);

        $presentedFile = new PresentedFile($this->context, $url, PresentedFile::FILE_TYPE_URL);

        $presentedFile->getFileContents();

        $this->assertEquals(md5($presentedFile->getFileContents()), $hash);
    }

    public function testWithFileContentImage()
    {
        $fileContent = file_get_contents(__DIR__ .'/../stub/logo.jpg');

        $hash = md5($fileContent);

        $presentedFile = new PresentedFile($this->context, $fileContent, PresentedFile::FILE_TYPE_CONTENT);

        $this->assertEquals(md5($presentedFile->getFileContents()), $hash);
    }

    public function testWithFileContentCsv()
    {
        $fileContent = file_get_contents(__DIR__ .'/../stub/asset_tags/asset_1/buy.csv');

        $hash = md5($fileContent);

        $presentedFile = new PresentedFile($this->context, $fileContent, PresentedFile::FILE_TYPE_CONTENT, 'buy.csv');

        $this->assertEquals(md5($presentedFile->getFileContents()), $hash);

        $this->assertTrue(Str::endsWith($presentedFile->getFileName(), '.csv'));
    }

    public function testWithFilePath()
    {
        $fileContent = file_get_contents($filePath = __DIR__ .'/../stub/logo.jpg');

        $hash = md5($fileContent);

        $presentedFile = new PresentedFile($this->context, $filePath, PresentedFile::FILE_TYPE_PATH);

        $this->assertEquals(md5($presentedFile->getFileContents()), $hash);

        $this->assertTrue(Str::endsWith($presentedFile->getFileName(), ['.jpg', '.jpeg']));

    }

    public function testWithBase64Encode()
    {
        $fileContent = base64_encode($content = file_get_contents($filePath = __DIR__ .'/../stub/logo.jpg'));

        $hash = md5($content);

        $presentedFile = new PresentedFile($this->context, $fileContent, PresentedFile::FILE_TYPE_BASE64);

        $this->assertEquals(md5($presentedFile->getFileContents()), $hash);

        $this->assertTrue(Str::endsWith($presentedFile->getFileName(), ['.jpg', '.jpeg']));

    }
}