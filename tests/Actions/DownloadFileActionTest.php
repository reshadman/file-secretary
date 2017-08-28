<?php

namespace FileSecretaryTests\Actions;

use FileSecretaryTests\BaseTestCase;
use Reshadman\FileSecretary\Application\AddressableRemoteFile;
use Reshadman\FileSecretary\Application\PresentedFile;
use Reshadman\FileSecretary\Application\Privacy\PublicPrivacy;
use Reshadman\FileSecretary\Application\Usecases\StoreFile;
use Reshadman\FileSecretary\Infrastructure\FileSecretaryManager;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class DownloadFileActionTest extends BaseTestCase
{
    const PRIVATE_BASIC_FILE = 'file_manager_private';
    const PRIVATE_IMAGE = 'images_private';

    const PUBLIC_BASIC_FILE = 'file_manager_public';
    const PUBLIC_IMAGE = 'images_public';

    /**
     * @var PresentedFile
     */
    private $presented;

    /**
     * @var AddressableRemoteFile
     */
    private $addressable;

    public function testForBasicFilePublic()
    {
        $this->storeMain($c = static::PUBLIC_BASIC_FILE);

        $resp = $this->get(route('file-secretary.get.download_file', [
            'context_name' => $c,
            'context_folder' => $this->manager()->getContextData($c)['context_folder'],
            'after_context_path' => $this->presented->getFileName(true)
        ]));

        $resp->assertResponseOk();
        $resp->assertResponseStatus(200);

        $resp->see($this->presented->getFileContents());

        $resp->seeHeader('Content-Type', $this->presented->getMimeType());
    }

    public function testForBasicFilePrivate()
    {
        $this->expectException(HttpExceptionInterface::class);

        $this->storeMain($c = static::PRIVATE_BASIC_FILE);

        $this->get(route('file-secretary.get.download_file', [
            'context_name' => $c,
            'context_folder' => $this->manager()->getContextData($c)['context_folder'],
            'after_context_path' => $this->presented->getFileName(true)
        ]));
    }

    public function testForMainImagePublic()
    {
        $this->storeMain($c = static::PUBLIC_IMAGE);

        $resp = $this->get(route('file-secretary.get.download_file', [
            'context_name' => $c,
            'context_folder' => $this->manager()->getContextData($c)['context_folder'],
            'after_context_path' => $this->presented->getSiblingFolder() . '/' . $this->presented->getFileName(true)
        ]));

        $resp->assertResponseOk();
        $resp->assertResponseStatus(200);

        $resp->see($this->presented->getFileContents());

        $resp->seeHeader('Content-Type', $this->presented->getMimeType());
    }

    public function testForMainImagePrivate()
    {
        $this->expectException(HttpExceptionInterface::class);

        $this->storeMain($c = static::PRIVATE_IMAGE);

        $this->get(route('file-secretary.get.download_file', [
            'context_name' => $c,
            'context_folder' => $this->manager()->getContextData($c)['context_folder'],
            'after_context_path' => $this->presented->getSiblingFolder() . '/' . $this->presented->getFileName(true)
        ]));
    }

    public function testForTemplateImageNoStore()
    {
        $this->storeMain($c = static::PUBLIC_IMAGE);

        $resp = $this->get(route('file-secretary.get.download_file', [
            'context_name' => $c,
            'context_folder' => $this->manager()->getContextData($c)['context_folder'],
            'after_context_path' => $this->presented->getSiblingFolder() . '/' . $this->presented->getFileName(true)
        ]));

        $resp->assertResponseOk();
        $resp->assertResponseStatus(200);

        $resp->see($this->presented->getFileContents());

        $resp->seeHeader('Content-Type', $this->presented->getMimeType());

        $resp2 = $this->get(route('file-secretary.get.download_file', [
            'context_name' => $c,
            'context_folder' => $this->manager()->getContextData($c)['context_folder'],
            'after_context_path' => $this->presented->getSiblingFolder() . '/' . ($name = 'companies_logo_201xauto.png')
        ]));

        $resp2->seeHeader('Content-Type', $this->manager()->getMimeForExtension('png'));

        $this->assertFalse($this->manager()->getContextDriver(static::PUBLIC_IMAGE)->exists(
            $this->manager()->getContextStartingPath(static::PUBLIC_IMAGE) . '/' . $this->presented->getSiblingFolder() . '/' . $name
        ));
    }

    public function testForTemplateImageStoreBesideMain()
    {
        $config = $this->manager()->getConfig();
        $config['contexts'][static::PUBLIC_IMAGE]['store_manipulated'] = true;
        $this->manager()->reInitConfig($config);

        $this->storeMain($c = static::PUBLIC_IMAGE);

        $resp = $this->get(route('file-secretary.get.download_file', [
            'context_name' => $c,
            'context_folder' => $this->manager()->getContextData($c)['context_folder'],
            'after_context_path' => $this->presented->getSiblingFolder() . '/' . $this->presented->getFileName(true)
        ]));

        $resp->assertResponseOk();
        $resp->assertResponseStatus(200);

        $resp->see($this->presented->getFileContents());

        $resp->seeHeader('Content-Type', $this->presented->getMimeType());

        $resp2 = $this->get(route('file-secretary.get.download_file', [
            'context_name' => $c,
            'context_folder' => $this->manager()->getContextData($c)['context_folder'],
            'after_context_path' => $this->presented->getSiblingFolder() . '/' . ($name = 'companies_logo_201xauto.png')
        ]));

        $resp2->seeHeader('Content-Type', $this->manager()->getMimeForExtension('png'));

        $this->assertTrue($this->manager()->getContextDriver(static::PUBLIC_IMAGE)->exists(
            $this->manager()->getContextStartingPath(static::PUBLIC_IMAGE) . '/' . $this->presented->getSiblingFolder() . '/' . $name
        ));
    }

    public function testForTemplateImageStoreSomewhereElse()
    {
        $this->storeMain($c = 'images_with_another_context_for_manipulated');

        $resp = $this->get(route('file-secretary.get.download_file', [
            'context_name' => $c,
            'context_folder' => $this->manager()->getContextData($c)['context_folder'],
            'after_context_path' => $this->presented->getSiblingFolder() . '/' . $this->presented->getFileName(true)
        ]));

        $resp->assertResponseOk();
        $resp->assertResponseStatus(200);

        $resp->see($this->presented->getFileContents());

        $resp->seeHeader('Content-Type', $this->presented->getMimeType());

        $c2 = $this->manager()->getContextData($c)['store_manipulated'];
        $resp2 = $this->get(route('file-secretary.get.download_file', [
            'context_name' => $c2,
            'context_folder' => $this->manager()->getContextData($c2)['context_folder'],
            'after_context_path' => $this->presented->getSiblingFolder() . '/' . ($name = 'companies_logo_201xauto.png')
        ]));

        $resp2->seeHeader('Content-Type', $this->manager()->getMimeForExtension('png'));

        $this->assertTrue($this->manager()->getContextDriver($c)->exists(
            $this->manager()->getContextStartingPath($c2) . '/' . $this->presented->getSiblingFolder() . '/' . $name
        ));
    }

    public function testExceptionWhenMiddlewareIsNotSet()
    {
        $this->expectException(\LogicException::class);

        $this->withoutMiddleware();

        $this->storeMain($c = 'images_with_another_context_for_manipulated');

        $this->get(route('file-secretary.get.download_file', [
            'context_name' => $c,
            'context_folder' => $this->manager()->getContextData($c)['context_folder'],
            'after_context_path' => $this->presented->getSiblingFolder() . '/' . $this->presented->getFileName(true)
        ]));
    }

    public function storeMain($context)
    {
        /** @var StoreFile $store */
        $store = app(StoreFile::class);

        $r = $store->execute($p = new PresentedFile(
            $context,
            __DIR__ . '/../../stub/logo.jpg',
            PresentedFile::FILE_TYPE_PATH
        ));

        $this->presented = $p;
        $this->addressable = $r;

        return [
            $p,
            $r
        ];
    }

    /**
     * @return FileSecretaryManager
     */
    public function manager()
    {
        return app(FileSecretaryManager::class);
    }
}