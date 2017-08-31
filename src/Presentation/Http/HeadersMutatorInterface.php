<?php

namespace Reshadman\FileSecretary\Presentation\Http;

use Reshadman\FileSecretary\Application\PrivacyCheckNeeds;

interface HeadersMutatorInterface
{
    public static function mutateHeaders(PrivacyCheckNeeds $needs, array $headers = []);
}