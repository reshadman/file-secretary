<?php

namespace Reshadman\FileSecretary\Application\Privacy;

class PublicPrivacy implements PrivacyInterface
{
    public function isAllowed()
    {
        return true;
    }
}