<?php

namespace Reshadman\FileSecretary\Application;

class PrivacyCheckNeeds
{
    private $contextName;
    private $contextFolder;
    private $fileUuid;
    private $fileExtension;
    private $fileName;
    private $relativePath;

    public function __construct(
        $contextName,
        $contextFolder,
        $relativePath
    ){
        $this->contextName = $contextName;
        $this->contextFolder = $contextFolder;
        $this->relativePath = $relativePath;
        $this->extractArgs($relativePath);
    }

    private function extractArgs($relativePath)
    {
        $extension = explode('.', $relativePath);

        if (count($extension) > 1) {
            $this->fileExtension = array_pop($extension);
            $relativePath = implode('.', $relativePath);
        }

        $relativePath = trim($relativePath, ['/', DIRECTORY_SEPARATOR]);

        $exploded = explode('/', $relativePath);

        if (count($exploded) > 1) {
            $this->fileUuid = $exploded[0];
            $this->fileName = $exploded[1];
        } else {
            $this->fileUuid = $exploded[0];
            $this->fileName = $exploded[0];
        }
    }

    public function getContextName()
    {
        return $this->contextName;
    }

    public function getContextFolder()
    {
        return $this->contextFolder;
    }

    public function getFileUuid()
    {
        return $this->fileUuid;
    }

    public function getFileExtension()
    {
        return $this->fileExtension;
    }

    public function getFileName($ext = true)
    {
        return $this->fileName . ($ext ? ($this->fileExtension ? '.' . $this->fileExtension : '') : '');
    }

    public function getRelativePath()
    {
        return $this->relativePath;
    }
}