<?php

namespace Reshadman\FileSecretary\Presentation\Http;

use Reshadman\FileSecretary\Application\PrivacyCheckNeeds;

class CacheContentHeadersMutator implements HeadersMutatorInterface
{
    public static function mutateHeaders(PrivacyCheckNeeds $needs, array $headers = [])
    {
        $maxAge = 1900800; //60 * 60 * 24 * 22;

        return array_merge($headers, [
            'Pragma' => 'public',
            'Cache-Control' =>  'max-age=' . $maxAge,
            'Expires' => gmdate('D, d M Y H:i:s \G\M\T', time() + $maxAge)
        ]);
    }
}