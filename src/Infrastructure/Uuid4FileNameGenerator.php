<?php

namespace Reshadman\FileSecretary\Infrastructure;

use Ramsey\Uuid\Uuid;
use Reshadman\FileSecretary\Application\FileUniqueIdGeneratorInterface;
use Reshadman\FileSecretary\Application\PresentedFile;

class Uuid4FileNameGenerator implements FileUniqueIdGeneratorInterface
{
    public static function generate(PresentedFile $presentedFile)
    {
        return Uuid::uuid4()->toString();
    }
}