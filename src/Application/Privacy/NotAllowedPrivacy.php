<?php

namespace Reshadman\FileSecretary\Application\Privacy;

class NotAllowedPrivacy implements PrivacyInterface
{
    public function isAllowed()
    {
        return false;
    }
}