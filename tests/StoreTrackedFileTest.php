<?php

namespace FileSecretaryTests;

use Reshadman\FileSecretary\Application\PersistableFile;
use Reshadman\FileSecretary\Application\PresentedFile;
use Reshadman\FileSecretary\Application\Usecases\StoreTrackedFile;

class StoreTrackedFileTest extends BaseTestCase
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
            "file_manager_public",
            $file,
            PresentedFile::FILE_TYPE_PATH,
            $org = 'org_name.png'
        ));

        $this->assertInstanceOf(PersistableFile::class, $response);

        $this->assertNotNull($response->getFileableIdentifier());

        $this->assertEquals($org, $response->getFileableOriginalName());

        $this->seeInDatabase('system__files', [
            'original_name' => $org,
            'uuid' => $p->getUuid()
        ]);


        $this->assertEquals(\DB::table('system__files')->count(), 1);

        $response2 = $storeCommand->execute($p);

        $this->assertEquals(\DB::table('system__files')->count(), 2);

        $this->assertFalse($response2->getFileableIdentifier() === $response->getFileableIdentifier());
    }
}