<?php

namespace Reshadman\FileSecretary\Application;

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
            . self::fileableTrim($this->getFileableFullFileName());

        return $path;
    }

    /**
     * Trim
     *
     * @param $what
     * @return string
     */
    private static function fileableTrim($what)
    {
        return trim($what, '/');
    }

    /**
     * Get full file name.
     *
     * @return string
     */
    public function getFileableFullFileName()
    {
        $ext = $this->getFileableExtension();

        if ($ext !== null && $ext !== '') {
            return $this->getFileableFileName() . '.' . $ext;
        }

        return $this->getFileableFileName();
    }

    public function getFileableFileUniqueIdentifier()
    {
        $sibling = $this->getFileableSiblingFolder();

        if ($sibling !== null) {
            return $sibling;
        }

        return $this->getFileableFileName();
    }
}