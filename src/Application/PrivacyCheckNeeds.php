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
        $relativePath = trim($relativePath, '/');

        $pathInfo = pathinfo($relativePath);

        $this->fileName = $pathInfo['filename'];

        $this->fileExtension = $pathInfo['extension'];

        if (!in_array($pathInfo['dirname'], ['/', '.', '', DIRECTORY_SEPARATOR, '..'])) {
            $this->fileUuid = $pathInfo['dirname'];
        } else {
            $this->fileUuid = $this->fileName;
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