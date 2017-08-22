<?php

namespace Reshadman\FileSecretary\Application\Events;

class BeforeAssetUpload
{
    public $assetTag;
    public $uniqueName;

    public function __construct($assetTag, $uniqueName)
    {
        $this->assetPath = $assetTag;
        $this->uniqueName = $uniqueName;
    }
}