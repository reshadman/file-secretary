<?php

namespace Reshadman\FileSecretary\Application\Privacy;

use Reshadman\FileSecretary\Application\PrivacyCheckNeeds;

interface PrivacyInterface
{
    public function isAllowed(PrivacyCheckNeeds $needs);
}