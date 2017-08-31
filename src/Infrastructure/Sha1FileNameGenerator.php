<?php

namespace Reshadman\FileSecretary\Infrastructure;

use Reshadman\FileSecretary\Application\FileUniqueIdGeneratorInterface;
use Reshadman\FileSecretary\Application\PresentedFile;

class Sha1FileNameGenerator implements FileUniqueIdGeneratorInterface
{
    public static function generate(PresentedFile $presentedFile)
    {
        $size = $presentedFile->getFileInstance()->getSize();
        $hash = sha1_file($presentedFile->getFileInstance()->getPath());
        return  $size . '-' . $hash;
    }
}