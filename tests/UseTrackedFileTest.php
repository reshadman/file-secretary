<?php

namespace FileSecretaryTests;

use Reshadman\FileSecretary\Application\PresentedFile;
use Reshadman\FileSecretary\Application\Usecases\StoreTrackedFile;
use Reshadman\FileSecretary\Application\Usecases\UseOrUnuseTrackedFile;

class UseTrackedFileTest extends BaseTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/../fixtures/migrations');

        $this->artisan('migrate');
    }

    public function testWorksAsExpected()
    {
        $file = __DIR__ . '/../stub/logo.jpg';

        /** @var StoreTrackedFile $storeCommand */
        $storeCommand = app(StoreTrackedFile::class);

        $response = $storeCommand->execute($p = new PresentedFile(
            "images_public",
            $file,
            PresentedFile::FILE_TYPE_PATH,
            $org = 'org_name.png'
        ));

        $this->seeInDatabase('system__files', [
            'id' => $response->getFileableIdentifier(),
            'used_times' => 0
        ]);

        /** @var UseOrUnuseTrackedFile $command */
        $command = app(UseOrUnuseTrackedFile::class);
        $command->execute($response->getFileableUuid());

        $this->seeInDatabase('system__files', [
            'id' => $response->getFileableIdentifier(),
            'used_times' => 1
        ]);

        $command->execute($response);

        $this->seeInDatabase('system__files', [
            'id' => $response->getFileableIdentifier(),
            'used_times' => 2
        ]);
    }
}