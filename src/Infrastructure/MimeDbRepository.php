<?php

namespace Reshadman\FileSecretary\Infrastructure;

use MimeTyper\Repository\MimeDbRepository as BaseMimeDbRepository;

class MimeDbRepository extends BaseMimeDbRepository
{
    protected $extendedMimes = [
        'application/x-rar-compressed' => 'rar',
        'application/rar' => 'rar'
    ];

    public function findExtension($type)
    {
        $found = parent::findExtension($type);

        if ($found === null) {
            if (isset($this->extendedMimes[$type])) {
                $found = $this->extendedMimes[$type];
            }
        }

        return FileSecretaryManager::normalizeExtension($found);
    }

    public function findType($extension)
    {
        // Get all matching extensions.
        $types = $this->findTypes($extension);

        if (count($types) > 0) {
            // Return first match.
            return $types[0];
        }

        return null;
    }
}