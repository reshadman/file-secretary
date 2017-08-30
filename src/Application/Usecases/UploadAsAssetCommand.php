<?php

namespace Reshadman\FileSecretary\Application\Usecases;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemAdapter;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Rackspace\RackspaceAdapter;
use Reshadman\FileSecretary\Application\Events\AfterAssetUpload;
use Reshadman\FileSecretary\Application\Events\BeforeAssetUpload;
use Reshadman\FileSecretary\Infrastructure\FileSecretaryManager;
use Reshadman\FileSecretary\Infrastructure\Rackspace\DirectoryPush;

class UploadAsAssetCommand
{
    protected $config = [];

    /**
     * @var Dispatcher
     */
    private $events;
    /**
     * @var FileSecretaryManager
     */
    private $secretaryManager;
    /**
     * @var Filesystem
     */
    private $nativeFiles;

    public function __construct(
        Dispatcher $events,
        FileSecretaryManager $secretaryManager,
        Filesystem $nativeFiles
    ) {
        $this->events = $events;
        $this->secretaryManager = $secretaryManager;
        $this->nativeFiles = $nativeFiles;
    }

    /**
     * Execute the command.
     *
     * @param $assetTag
     * @return int
     */
    public function execute($assetTag)
    {
        $this->config = $this->secretaryManager->getConfig();

        $tagData = array_get($this->config, 'asset_folders.' . $assetTag);

        $this->checkExistence($tagData);

        /** @var FilesystemAdapter $driver */
        $driver = $this->secretaryManager->getContextDriver($tagData['context']);

        $newVersionPath = '/' . $this->secretaryManager->getAssetStartingPath($tagData['context'],
                $assetTag) . '/' . ($uniqueName = time()) . '/' . $tagData['after_public_path'];

        $this->events->fire(new BeforeAssetUpload($assetTag, $uniqueName));

        /** @var AdapterInterface $adapter */
        $adapter = $driver->getDriver()->getAdapter();

        $directoriesToUpload = [];

        // If only some directories are allowed to be uploaded.
        $only = array_get($tagData, 'only_directories');

        if ($only === null) {
            $directoriesToUpload = [
                ['full' => $tagData['path'],  'relative' => '']
            ];
        } else {
            $dirs = $this->nativeFiles->directories($tagData['path']);

            foreach ($dirs as $check) {
                foreach ($only as $needed) {
                    if ($check === ($tagData['path'] . '/' . $needed)) {
                        $directoriesToUpload[] = [
                            'full' => $check,
                            'relative' => '/' . $needed
                        ];
                        break;
                    }
                }
            }
        }

        foreach ($directoriesToUpload as $dirToUpload) {
            $fullDir = $dirToUpload['full'];
            if ($adapter instanceof RackspaceAdapter) {
                DirectoryPush::factory(
                    $fullDir,
                    $adapter->getContainer(),
                    $newVersionPath . $dirToUpload['relative']
                )->execute();
            } else {
                foreach ($this->nativeFiles->allFiles($fullDir) as $file) {

                    $append = $this->secretaryManager->replaceFirst($fullDir, '', $file);

                    $driver->put($newVersionPath . $dirToUpload['relative'] . '/' . $append, $this->nativeFiles->get($file));
                }
            }
        }


        $this->events->fire(new AfterAssetUpload($assetTag, $uniqueName));

        return $uniqueName;
    }

    /**
     * Check existence
     *
     * @param $tagData
     */
    protected function checkExistence($tagData)
    {
        if ( ! $this->nativeFiles->exists($tagData['path'])) {
            throw new \InvalidArgumentException("Path {$tagData['path']} not found.");
        }
    }
}