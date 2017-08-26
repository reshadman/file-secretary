<?php

namespace FileSecretaryTests;

use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use Reshadman\FileSecretary\Application\AddressableRemoteFile;
use Reshadman\FileSecretary\Application\PresentedFile;
use Reshadman\FileSecretary\Application\Usecases\MakeImage;
use Reshadman\FileSecretary\Application\Usecases\StoreFile;
use Reshadman\FileSecretary\Infrastructure\FileSecretaryManager;
use Reshadman\FileSecretary\Infrastructure\Images\FileSecretaryImageManager;

class ImageManagerTest extends BaseTestCase
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

        $this->imageToStore = __DIR__ .'/../stub/logo.jpg';

        /** @var StoreFile $store */
        $this->store = app(StoreFile::class);

        $this->remoteFile = $this->store->execute(new PresentedFile(
            "images_public",
            $this->imageToStore,
            PresentedFile::FILE_TYPE_PATH,
            "logo.png"
        ));


        /** @var MakeImage $make */
        $this->make = app(MakeImage::class);


        /** @var FileSecretaryManager $secManager */
        $this->secManager = app(FileSecretaryManager::class);
        $this->contents = $this->secManager->getContextDriver("images_public")->get($this->remoteFile->fullRelative());

        $this->im = app(ImageManager::class);


    }

    public function testExtensionEqualityIsCheckedCorrectly()
    {
        $this->assertTrue(FileSecretaryImageManager::extensionsAreEqual('jpg', 'jpg'));

        $this->assertTrue(FileSecretaryImageManager::extensionsAreEqual('jpg', 'JPG'));

        $this->assertFalse(FileSecretaryImageManager::extensionsAreEqual('png', 'jpg'));
    }

    public function testImageIsResizedWithNoException()
    {

        $made = $this->make->execute($this->contents, "companies_logo_200x200", "jpg");

        $this->assertTrue(true, "Successful Generation.");

        $image = $this->im->make($made->image());

        $this->assertContains("image/jpeg", strtolower($image->mime()));

        $this->assertEquals($image->getWidth(), 200);
    }

    public function testThrowsExceptionWhenNullEncodingInConfigAndWrongEncodingWanted()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->make->execute($this->contents, "companies_logo_200x200", "png");
    }

    public function testThrowsExceptionWhenInvalidTemplateGiven()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->make->execute($this->contents, "companies_logo_200x201", "jpg");
    }

    public function testWithEncoding()
    {
        $made = $this->make->execute($this->contents, "companies_logo_201x201", "png");

        $this->assertContains("png", $this->im->make($made->image())->mime());
    }

    public function testThrowsExceptionWhenExtensionIsNotAvailable()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->make->execute($this->contents, "companies_logo_201x201", "jpg");
    }
}