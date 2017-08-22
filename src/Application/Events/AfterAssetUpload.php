<?php

namespace Reshadman\FileSecretary\Application\Events;

class AfterAssetUpload
{
    public $newUniqueKey;
    public $assetTag;

    public function __construct($assetTag, $newUniqueKey)
    {
        $this->newUniqueKey = $newUniqueKey;
        $this->assetTag = $assetTag;
    }
}