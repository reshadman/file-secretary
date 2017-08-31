<?php

namespace Reshadman\FileSecretary\Application;

interface FileUniqueIdGeneratorInterface
{
    public static function generate(PresentedFile $presentedFile);
}