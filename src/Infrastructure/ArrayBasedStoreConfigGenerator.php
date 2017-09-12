<?php

namespace Reshadman\FileSecretary\Infrastructure;

use Reshadman\FileSecretary\Application\StoreConfigGeneratorInterface;

class ArrayBasedStoreConfigGenerator implements StoreConfigGeneratorInterface
{
    /**
     * @var FileSecretaryManager
     */
    private $manager;

    public function __construct(FileSecretaryManager $manager)
    {
        $this->manager = $manager;
    }

    public function generateForContext($contextName, $contextData)
    {
        $data = array_get($this->manager->getContextData($contextName), 'store_config_array');

        if (!is_array($data)) {
            $data = $this->manager->getConfig("default_store_config_array");

            if (!is_array($data)) {
                $data = [];
            }
        }

        return $data;
    }
}