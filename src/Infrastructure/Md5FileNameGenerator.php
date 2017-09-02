<?php

namespace Reshadman\FileSecretary\Infrastructure;

use Reshadman\FileSecretary\Application\FileUniqueIdGeneratorInterface;
use Reshadman\FileSecretary\Application\PresentedFile;

class Md5FileNameGenerator implements FileUniqueIdGeneratorInterface
{
    public static function generate(PresentedFile $presentedFile)
    {
        $size = $presentedFile->getFileInstance()->getSize();
        $hash = md5_file($presentedFile->getFileInstance()->getRealPath());
        return  $size . '-' . $hash;
    }
}