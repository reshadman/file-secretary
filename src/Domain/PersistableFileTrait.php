<?php

namespace Reshadman\FileSecretary\Domain;

trait PersistableFileTrait
{
    /**
     * Get final path of the file.
     *
     * @return string
     */
    public function getFinalPath()
    {
        $path = self::fileableTrim($this->getFileableContextFolder())
            . '/'
            . self::fileableTrim($this->getFileableSiblingFolder())
            . '/'
            . self::fileableTrim($this->getFileableFileName());

        return $path;
    }

    private static function fileableTrim($what)
    {
        return trim($what, '/');
    }
}