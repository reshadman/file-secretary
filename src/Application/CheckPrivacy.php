<?php

namespace Reshadman\FileSecretary\Application;

use Reshadman\FileSecretary\Application\Privacy\PrivacyInterface;
use Reshadman\FileSecretary\Infrastructure\FileSecretaryManager;

class CheckPrivacy
{
    /**
     * @var FileSecretaryManager
     */
    private $secretaryManager;

    public function __construct(FileSecretaryManager $secretaryManager)
    {

        $this->secretaryManager = $secretaryManager;
    }

    public function execute(PrivacyCheckNeeds $needs)
    {
        $contextData = $this->secretaryManager->getContextData($needs->getContextName());

        $privacyClass = $contextData['privacy'];

        /** @var PrivacyInterface $privacyObject */
        $privacyObject = app($privacyClass);

        return $privacyObject->isAllowed($needs);
    }
}