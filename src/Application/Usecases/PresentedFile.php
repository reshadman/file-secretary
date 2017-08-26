<?php

namespace Reshadman\FileSecretary\Application\Usecases;

use GuzzleHttp\Client;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use MimeTyper\Repository\MimeDbRepository;
use Ramsey\Uuid\Uuid;
use Reshadman\FileSecretary\Domain\ContextTypes;
use Reshadman\FileSecretary\Domain\FileSecretaryManager;
use Symfony\Component\HttpFoundation\File\File;

class PresentedFile
{
    private static $mimeDb;
    /**
     * @var File|mixed
     */
    private $file;
    private $fileType;
    private $uniqueName;
    private $fileContents;
    private $newPath;
    private $uuid;
    private $payload;

    const FILE_TYPE_URL = 'url';
    const FILE_TYPE_CONTENT = 'content';
    const FILE_TYPE_INSTANCE = 'instance';
    const FILE_TYPE_PATH = 'path';
    const FILE_TYPE_BASE64 = 'base64';

    const MAIN_IMAGE_NAME = 'main';

    /**
     * @var null
     */
    private $originalName;

    private $context;

    /**
     * PresentedFile constructor.
     * @param $context
     * @param $file
     * @param $fileType
     * @param null $originalName
     * @param array $payload
     */
    public function __construct($context, $file, $fileType, $originalName = null, array $payload = [])
    {
        $this->context = $context;
        $this->file = $file;
        $this->fileType = $fileType;
        $this->payload = $payload;
        $this->originalName = $originalName;
        $this->uniqueName = Uuid::uuid4()->toString();
        $this->resolveFile();
    }

    /**
     * The generic file instance
     *
     * @return File
     */
    public function getFileInstance()
    {
        return $this->getResolvedFile();
    }

    /**
     * Get file type
     *
     * @return string
     */
    public function getFileFileType()
    {
        return $this->fileType;
    }

    /**
     * Get guessed file mime extension.
     *
     * @return null|string
     */
    public function getMimeType()
    {
        return $this->getResolvedFile()->getMimeType();
    }

    /**
     * Get file name including the extension
     *
     * @param bool $ext
     * @return string
     */
    public function getFileName($ext = true)
    {
        return $this->getFileInstance()->getBasename() . ($ext ? '.' . $this->getFileExtension() : '');
    }

    /**
     * Get file extension
     *
     * @return null|string
     */
    public function getFileExtension()
    {
        $mime = $this->getFileInstance()->getMimeType();

        if (empty($mime)) {
            return $this->getOriginalNameExtension();
        }

        if (Str::startsWith($mime, 'text/') && $this->originalName !== null) {
            return $this->getOriginalNameExtension();
        }

        return self::getMimeDb()->findExtension($mime);
    }

    /**
     * Is for given file type.
     *
     * @param $fileType
     * @return bool
     */
    protected function isFileType($fileType)
    {
        return $this->getFileFileType() === $fileType;
    }

    /**
     * @return Filesystem
     */
    protected function getNativeFilesInstance()
    {
        return app(Filesystem::class);
    }

    /**
     * Resolve given file to the proper instance
     *
     * @return void
     */
    private function resolveFile()
    {
        if ($this->isFileType(static::FILE_TYPE_INSTANCE)) {
            $path = $this->file->getPath();
            $this->fileContents = function () use($path) {
                return $this->getNativeFilesInstance()->get($path);
            };
            return;
        }

        if ($this->isFileType(static::FILE_TYPE_BASE64)) {
            $this->file = $this->fileContents = base64_decode($this->file);
            $this->file = new File($this->putTemp($this->file));
            return;
        }

        if ($this->isFileType(static::FILE_TYPE_CONTENT)) {
            $this->fileContents = $this->file;
            $this->file = new File($this->putTemp($this->file));
            return;
        }

        if ($this->isFileType(static::FILE_TYPE_PATH)) {
            $this->fileContents = $this->getNativeFilesInstance()->get($this->file);
            $this->file = new File($this->file);
            return;
        }

        $url = $this->file;
        $this->file = function () use($url) {
            return new File($this->downloadFile($url));
        };
    }

    /**
     * Put temporary.
     *
     * @param $content
     * @param null $extension
     * @return string
     */
    protected function putTemp($content, $extension = null)
    {
        $path = sys_get_temp_dir() . '/' . $this->uniqueName;

        if ($extension) {
            $path = $this->uniqueName . '.' . $extension;
        }

        $this->getNativeFilesInstance()->put($path, $content);

        return $path;
    }

    /**
     * Download the file.
     *
     * @param $file
     * @return string
     */
    protected function downloadFile($file)
    {
        if (filter_var($file, FILTER_VALIDATE_URL) === false) {
            throw new \InvalidArgumentException("The given file");
        }

        $stream = $path = sys_get_temp_dir() . '/' . Uuid::uuid4()->toString();

        $client = (new Client);

        $response = $client->get($file, [
            'sink' => $stream
        ]);

        $this->fileContents = function () use($response, $stream) {
            return $response->getBody()->getContents();
        };

        return $path;
    }

    /**
     * @return File
     */
    private function getResolvedFile()
    {
        if ($this->file instanceof \Closure) {
            $this->file = call_user_func($this->file);
        }

        return $this->file;
    }

    public function getFileContents()
    {
        $this->getFileInstance();

        if ($this->fileContents instanceof \Closure) {
            $this->fileContents = call_user_func($this->fileContents);
        }

        return $this->fileContents;
    }

    /**
     * @return MimeDbRepository
     */
    private static function getMimeDb()
    {
        if (null === self::$mimeDb) {
            self::$mimeDb = new  MimeDbRepository();
        }

        return self::$mimeDb;
    }

    private function getOriginalNameExtension()
    {
        $ext = explode('.', $this->originalName);

        if (empty($ext)) {
            return null;
        }

        return array_pop($ext);
    }

    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    public function getContextDriver()
    {
        return $this->getSecretaryManager()->getContextDriver($this->getContext());
    }

    /**
     * Get file secretary manager instance
     *
     * @return FileSecretaryManager
     */
    protected function getSecretaryManager()
    {
        return app(FileSecretaryManager::class);
    }

    public function getNewPath()
    {
        if ($this->newPath !== null) {
            return $this->newPath;
        }

        $uuid = $this->getUuid();

        $ext = $this->getFileExtension();

        if ($ext) {
            $ext = '.' .$ext;
        } else {
            $ext = '';
        }

        $contextData = $this->getSecretaryManager()->getConfig("contexts." . $this->getContext());

        if ($contextData['category'] === ContextTypes::TYPE_IMAGE) {
            $newPath = $uuid . '/' . $this->getImageName(static::MAIN_IMAGE_NAME . $ext);
        } elseif ($contextData['category'] === ContextTypes::TYPE_BASIC_FILE) {
            $newPath = $uuid . $ext ;
        } else {
            throw new \ErrorException("Context category is not supported.");
        }

        $this->newPath = $newPath;

        return $this->newPath;
    }

    public function getFullDriverPath()
    {
        return $this->getSecretaryManager()->getContextStartingPath($this->getContext()) . '/' . $this->getNewPath();
    }

    public function getContextData()
    {
        return $this->getSecretaryManager()->getConfig("contexts." . $this->getContext());
    }

    public function getUuid()
    {
        if ($this->uuid !== null) {
            return $this->uuid;
        }

        return $this->uuid = $this->getSecretaryManager()->getConfig("file_name_generator")($this);
    }

    public function getOriginalName($fallback = true)
    {
        return $this->originalName ?: ($fallback ? $this->getFileName() : null);
    }

    public function getSiblingFolder()
    {
        $contextData = $this->getSecretaryManager()->getConfig("contexts." . $this->getContext());

        if ($contextData['category'] === ContextTypes::TYPE_IMAGE) {
            return $this->getUuid();
        }

        return null;
    }

    public function getContextFolder()
    {
        $contextData = $this->getSecretaryManager()->getConfig("contexts." . $this->getContext());

        return array_get($contextData, 'context_folder');
    }

    public function getMd5Hash()
    {
        return md5($this->getFileContents());
    }

    public function getSha1Hash()
    {
        return sha1($this->getFileContents());
    }

    public function getCategory()
    {
        $contextData = $this->getSecretaryManager()->getConfig("contexts." . $this->getContext());

        return array_get($contextData, 'category');
    }

    public function getPayload()
    {
        return $this->payload;
    }

    public function getPayloadByKey()
    {
        return array_get($this->getPayload(), 'client_ip');
    }

    /**
     * @param $default
     * @return string
     */
    protected function getImageName($default)
    {
        $payload = $this->getPayload();

        if (!is_array($this->payload)) {
            return $default;
        }

        return array_get($payload, 'image_template_name', $default);
    }
}