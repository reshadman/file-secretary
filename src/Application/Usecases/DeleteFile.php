<?php

namespace Reshadman\FileSecretary\Application\Usecases;

use Reshadman\FileSecretary\Domain\ContextTypes;
use Reshadman\FileSecretary\Domain\FileSecretaryManager;

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

        if ($contextData['category'] === ContextTypes::TYPE_IMAGE) {
            $exploded = explode('.', $fullPath);
            array_pop($exploded);
            $exploded = explode('/', implode('', $exploded));
            array_pop($exploded);
            $driver->deleteDirectory(implode('', $exploded));
        } else {
            $driver->delete($fullPath);
        }
    }
}