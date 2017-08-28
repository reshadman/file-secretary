<?php

namespace Reshadman\FileSecretary\Application\Privacy;

use Reshadman\FileSecretary\Application\PrivacyCheckNeeds;

class PublicPrivacy implements PrivacyInterface
{
    public function isAllowed(PrivacyCheckNeeds $needs)
    {
        return true;
    }
}