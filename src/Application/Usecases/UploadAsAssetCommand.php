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

        if ($adapter instanceof RackspaceAdapter) {
            DirectoryPush::factory(
                $tagData['path'],
                $adapter->getContainer(),
                $newVersionPath
            )->execute();
        } else {
            foreach ($this->nativeFiles->allFiles($tagData['path']) as $file) {

                $append = $this->secretaryManager->replaceFirst($tagData['path'], '', $file);

                $driver->put($newVersionPath . '/' . $append, $this->nativeFiles->get($file));
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