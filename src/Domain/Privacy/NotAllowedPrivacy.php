<?php

namespace Reshadman\FileSecretary\Domain\Privacy;

class NotAllowedPrivacy implements PrivacyInterface
{
    public function isAllowed()
    {
        return false;
    }
}