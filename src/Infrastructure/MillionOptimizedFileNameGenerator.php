<?php

namespace Reshadman\FileSecretary\Infrastructure;

use Reshadman\FileSecretary\Application\FileUniqueIdGeneratorInterface;
use Reshadman\FileSecretary\Application\PresentedFile;

class MillionOptimizedFileNameGenerator extends Sha1FileNameGenerator implements FileUniqueIdGeneratorInterface
{
    public static function generate(PresentedFile $presentedFile)
    {
        $fileName = parent::generate($presentedFile);

        $fileName = explode('-', $fileName);

        $dirTree = substr($fileName[1], 0, 3);
        $chars = implode('/', str_split($dirTree));

        return $chars . '/' . implode('-', $fileName);
    }
}