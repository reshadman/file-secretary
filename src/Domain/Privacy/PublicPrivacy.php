<?php

namespace Reshadman\FileSecretary\Domain\Privacy;

class PublicPrivacy implements PrivacyInterface
{
    public function isAllowed()
    {
        return true;
    }
}