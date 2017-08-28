<?php

namespace Reshadman\FileSecretary\Application\Privacy;

use Reshadman\FileSecretary\Application\PrivacyCheckNeeds;

class NotAllowedPrivacy implements PrivacyInterface
{
    public function isAllowed(PrivacyCheckNeeds $needs)
    {
        return false;
    }
}