<?php

namespace FileSecretaryTests;

use Reshadman\FileSecretary\Application\PresentedFile;
use Reshadman\FileSecretary\Application\Usecases\DeleteTrackedFile;
use Reshadman\FileSecretary\Application\Usecases\StoreTrackedFile;
use Reshadman\FileSecretary\Infrastructure\FileSecretaryManager;


class DeleteTrackedFileTest extends BaseTestCase
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

        $context = "file_manager_public";

        $pGenerator = function () use($file, $context) {
            return new PresentedFile(
                $context,
                $file,
                PresentedFile::FILE_TYPE_PATH,
                $org = 'org_name.png'
            );
        };

        $p = $pGenerator();
        $p2 = $pGenerator();

        $create = function () use($file, $context, $p) {
            /** @var StoreTrackedFile $storeCommand */
            $storeCommand = app(StoreTrackedFile::class);

            return $storeCommand->execute($p);
        };

        $response = $create();

        /** @var FileSecretaryManager $secretaryManager */
        $secretaryManager = app(FileSecretaryManager::class);

        $this->seeInDatabase('system__files', [
            'id' => $id = $response->getFileableIdentifier()
        ]);

        $this->assertTrue($secretaryManager->getContextDriver($context)->exists(
            $path = $response->getFileableContextFolder() . '/' . $response->getFileableSiblingFolder() . '/' . $response->getFileableFileName()
        ));

        /** @var DeleteTrackedFile $deleteCommand */
        $deleteCommand = app(DeleteTrackedFile::class);

        $deleteCommand->execute($response, DeleteTrackedFile::ON_DELETE_DELETE_REMOTE);

        $this->dontSeeInDatabase('system__files', [
            'id' => $id
        ]);

        $this->assertFalse($secretaryManager->getContextDriver($context)->exists($path));

        // If the uuid is a true uuid for file not just for name:
        if ($p->getUuid() === $p2->getUuid()) {
            $response = $create();

            $response2 = $create();

            $deleteCommand->execute($response, DeleteTrackedFile::ON_DELETE_DELETE_IF_NOT_IN_OTHERS);

            $this->dontSeeInDatabase('system__files', [
                'id' => $response->getFileableIdentifier()
            ]);

            $this->seeInDatabase('system__files', [
                'id' => $response2->getFileableIdentifier()
            ]);

            $this->assertTrue($secretaryManager->getContextDriver($context)->exists($path));

            $deleteCommand->execute($response2->getFileableUuid(), DeleteTrackedFile::ON_DELETE_IGNORE_REMOTE);

            $this->assertTrue($secretaryManager->getContextDriver($context)->exists($path));

            $response = $create();

            $deleteCommand->execute($response->getFileableUuid(), DeleteTrackedFile::ON_DELETE_DELETE_REMOTE);

            $this->assertFalse($secretaryManager->getContextDriver($context)->exists($path));
        }
    }
}