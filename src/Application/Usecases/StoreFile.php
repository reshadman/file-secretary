<?php

namespace Reshadman\FileSecretary\Application\Usecases;

use Illuminate\Filesystem\FilesystemAdapter;
use League\Flysystem\Rackspace\RackspaceAdapter;
use OpenCloud\ObjectStore\Resource\DataObject;
use Reshadman\FileSecretary\Application\AddressableRemoteFile;
use Reshadman\FileSecretary\Application\PresentedFile;
use Reshadman\FileSecretary\Application\StoreConfigGeneratorInterface;
use Reshadman\FileSecretary\Infrastructure\ArrayBasedStoreConfigGenerator;
use Reshadman\FileSecretary\Infrastructure\FileSecretaryManager;

class StoreFile
{
    /**
     * @var FileSecretaryManager
     */
    private $manager;

    public function __construct(FileSecretaryManager $manager)
    {
        $this->manager = $manager;
    }

    public function execute(PresentedFile $file)
    {
        /** @var FilesystemAdapter $driver */
        $driver = $file->getContextDriver();

        if ($adapter = $driver->getDriver()->getAdapter() instanceof RackspaceAdapter) {

            $config = $this->generateConfig($file);

            if (array_key_exists('headers', $config)) {
                $data['headers'] = DataObject::stockHeaders($config['headers']);
            }

            $driver->getDriver()->put(
                $file->getFullDriverPath(),
                $file->getFileContents(),
                $config
            );

        } else {

            $driver->put($file->getFullDriverPath(), $file->getFileContents());

        }

        return new AddressableRemoteFile($file->getContextData(), $file->getNewPath());
    }

    protected function generateConfig(PresentedFile $file): array
    {
        $storeGenerator = array_get($file->getContextData(), "store_config_generator");

        if ($storeGenerator === null) {
            $storeGenerator = $this->manager->getConfig("default_store_config_generator");
        }

        if ($storeGenerator === null) {
            $storeGenerator = ArrayBasedStoreConfigGenerator::class;
        }

        /** @var StoreConfigGeneratorInterface $configGenerator */
        $storeGenerator = app($storeGenerator);
        $config = $storeGenerator->generateForContext($file->getContext(), $file->getContextData());

        return $config;
    }
}