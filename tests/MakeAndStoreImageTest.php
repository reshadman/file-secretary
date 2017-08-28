<?php

namespace FileSecretaryTests;

use Intervention\Image\ImageManager;
use Ramsey\Uuid\Uuid;
use Reshadman\FileSecretary\Application\AddressableRemoteFile;
use Reshadman\FileSecretary\Application\PresentedFile;
use Reshadman\FileSecretary\Application\Usecases\MakeAndStoreImage;
use Reshadman\FileSecretary\Application\Usecases\MakeImage;
use Reshadman\FileSecretary\Application\Usecases\StoreFile;
use Reshadman\FileSecretary\Infrastructure\FileSecretaryManager;

class MakeAndStoreImageTest extends BaseTestCase
{
    private $imageToStore;

    /**
     * @var AddressableRemoteFile
     */
    private $remoteFile;

    /**
     * @var StoreFile
     */
    private $store;

    /**
     * @var MakeImage
     */
    private $make;

    /**
     * @var FileSecretaryManager
     */
    private $secManager;
    private $contents;

    /**
     * @var ImageManager
     */
    private $im;

    public function setUp()
    {
        parent::setUp();

        $this->imageToStore = __DIR__ . '/../stub/logo.jpg';

        /** @var StoreFile $store */
        $this->store = app(StoreFile::class);

        $this->remoteFile = $this->store->execute(new PresentedFile(
            "images_private",
            $this->imageToStore,
            PresentedFile::FILE_TYPE_PATH,
            "logo.png",
            [
                'uuid' => $this->uuid = Uuid::uuid4()->toString()
            ]
        ));


        /** @var MakeImage $make */
        $this->make = app(MakeImage::class);


        /** @var FileSecretaryManager $secManager */
        $this->secManager = app(FileSecretaryManager::class);
        $this->contents = $this->secManager->getContextDriver("images_private")->get($this->remoteFile->fullRelative());

        $this->im = app(ImageManager::class);

    }

    public function testImageIsManipulatedAndStoredCorrectly()
    {
        $imageable = file_get_contents(__DIR__ . '/../stub/logo.jpg');

        /** @var MakeAndStoreImage $store */
        $store = app(MakeAndStoreImage::class);

        $uuid = $this->uuid;

        $response = $store->execute("images_private", $uuid, $imageable, "companies_logo_200x200", "jpg");

        $this->assertContains($this->uuid, $response->getRemoteFile()->fullRelative());

        $this->assertContains($this->im->make($imageable)->mime(),
            $this->im->make($response->getMadeImageResponse()->image())->mime());

        $this->assertTrue($this->secManager->getContextDriver("images_private")->exists($response->getRemoteFile()->fullRelative()));
    }

    public function testItThrowsExceptionWhenNoExceptionGiven()
    {
        $this->expectException(\InvalidArgumentException::class);

        $imageable = file_get_contents(__DIR__ . '/../stub/logo.jpg');

        /** @var MakeAndStoreImage $store */
        $store = app(MakeAndStoreImage::class);

        $uuid = $this->uuid;

        $store->execute("images_private", $uuid, $imageable, "companies_logo_200x200", null);

    }

    public function testItThrowsExceptionWhenInvalidExtensionGiven()
    {
        $this->expectException(\InvalidArgumentException::class);

        $imageable = file_get_contents(__DIR__ . '/../stub/logo.jpg');

        /** @var MakeAndStoreImage $store */
        $store = app(MakeAndStoreImage::class);

        $uuid = $this->uuid;

        $store->execute("images_private", $uuid, $imageable, "companies_logo_200x200", 'png');
    }

    public function testDoesNotStoreIfConfigSays()
    {
        $this->remoteFile = $this->store->execute(new PresentedFile(
            "images_public",
            $this->imageToStore,
            PresentedFile::FILE_TYPE_PATH,
            "logo.png",
            [
                'uuid' => $this->uuid = Uuid::uuid4()->toString()
            ]
        ));

        $imageable = file_get_contents(__DIR__ . '/../stub/logo.jpg');

        /** @var MakeAndStoreImage $store */
        $store = app(MakeAndStoreImage::class);

        $uuid = $this->uuid;

        $response = $store->execute("images_public", $uuid, $imageable, "companies_logo_200x200", "jpg");

        $this->assertNull($response->getRemoteFile());
    }
}