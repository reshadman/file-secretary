<?php

namespace Reshadman\FileSecretary\Infrastructure;

use Reshadman\FileSecretary\Application\FileUniqueIdGeneratorInterface;
use Reshadman\FileSecretary\Application\PresentedFile;

class PhpUniqidFileNameGenerator implements FileUniqueIdGeneratorInterface
{
    public static function generate(PresentedFile $presentedFile)
    {
        return uniqid(rand(1000, 9999), true);
    }
}