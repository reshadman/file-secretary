<?php

namespace Reshadman\FileSecretary\Domain;

interface PersistedFileProvider
{
    /**
     * @param $uuid
     * @return PersistableFile
     */
    public function findByUuid($uuid);
}