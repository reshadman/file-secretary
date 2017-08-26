<?php

namespace Reshadman\FileSecretary\Infrastructure;

use MimeTyper\Repository\MimeDbRepository as BaseMimeDbRepository;

class MimeDbRepository extends BaseMimeDbRepository
{
    public function findExtension($type)
    {
        $found = parent::findExtension($type);

        return FileSecretaryManager::normalizeExtension($found);
    }
}