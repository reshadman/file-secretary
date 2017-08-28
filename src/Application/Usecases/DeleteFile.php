<?php

namespace Reshadman\FileSecretary\Application\Usecases;

use Reshadman\FileSecretary\Application\ContextCategoryTypes;
use Reshadman\FileSecretary\Infrastructure\FileSecretaryManager;

class DeleteFile
{
    /**
     * @var FileSecretaryManager
     */
    private $secretaryManager;

    public function __construct(FileSecretaryManager $secretaryManager)
    {
        $this->secretaryManager = $secretaryManager;
    }

    public function execute($context, $fullPath)
    {
        $fullPath = trim($fullPath, '/');

        $driver = $this->secretaryManager->getContextDriver($context);

        $contextData = $this->secretaryManager->getContextData($context);

        if ($contextData['category'] === ContextCategoryTypes::TYPE_IMAGE) {
            $storeManipulated = $this->secretaryManager->getConfig("contexts.{$context}.store_manipulated", true);

            $exploded = explode('.', $fullPath);
            array_pop($exploded);
            $exploded = explode('/', implode('', $exploded));
            array_pop($exploded);
            $driver->deleteDirectory($dir = implode('', $exploded));

            if (is_string($storeManipulated)) {
                $manipulatedDriver = $this->secretaryManager->getContextDriver($storeManipulated);
                $manipulatedDriver->deleteDirectory($dir);
            }
        } else {
            $driver->delete($fullPath);
        }
    }
}