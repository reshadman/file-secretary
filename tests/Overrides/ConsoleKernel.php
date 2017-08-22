<?php

namespace FileSecretaryTests\Overrides;

use Illuminate\Foundation\Console\Kernel;
use Reshadman\FileSecretary\Presentation\Console\UploadAssets;

class ConsoleKernel extends Kernel
{
    protected $commands = [
        UploadAssets::class
    ];
}