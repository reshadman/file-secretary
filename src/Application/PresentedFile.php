<?php

namespace Reshadman\FileSecretary\Application;

use GuzzleHttp\Client;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use Reshadman\FileSecretary\Infrastructure\FileSecretaryManager;
use Reshadman\FileSecretary\Infrastructure\MimeDbRepository;
use Symfony\Component\HttpFoundation\File\File;

class PresentedFile
{
    const FILE_TYPE_URL = 'url';
    const FILE_TYPE_CONTENT = 'content';
    const FILE_TYPE_INSTANCE = 'instance';
    const FILE_TYPE_PATH = 'path';
    const FILE_TYPE_BASE64 = 'base64';

    const MAIN_IMAGE_NAME = '1_main';

    private static $mimeDb;
    /**
     * @var File|mixed
     */
    private $file;
    private $fileType;
    private $tempUniqueName;
    private $fileContents;
    private $newPath;
    private $fileUniqueIdentifier;
    private $payload;
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
        $this->tempUniqueName = Uuid::uuid4()->toString();
        $this->fileUniqueIdentifier = array_get($payload, 'file_unique_identifier', null);
        $this->resolveFile();
    }

    /**
     * Resolve given file to the proper instance
     *
     * @return void
     */
    private function resolveFile()
    {
        if ($this->isFileType(static::FILE_TYPE_INSTANCE)) {
            $path = $this->file->getRealPath();
            $this->fileContents = function () use ($path) {
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
        $this->file = function () use ($url) {
            return new File($this->downloadFile($url));
        };
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
     * Get file type
     *
     * @return string
     */
    public function getFileFileType()
    {
        return $this->fileType;
    }

    /**
     * @return Filesystem
     */
    protected function getNativeFilesInstance()
    {
        return app(Filesystem::class);
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
        $path = sys_get_temp_dir() . '/' . $this->tempUniqueName;

        if ($extension) {
            $path = $this->tempUniqueName . '.' . $extension;
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
            throw new \InvalidArgumentException("The given file is not a url path.");
        }

        $stream = $path = sys_get_temp_dir() . '/' . Uuid::uuid4()->toString();

        $client = (new Client);

        $response = $client->get($file, [
            'sink' => $stream
        ]);

        $this->fileContents = function () use ($response, $stream) {
            return $response->getBody()->getContents();
        };

        return $path;
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
     * @return File
     */
    private function getResolvedFile()
    {
        if ($this->file instanceof \Closure) {
            $this->file = call_user_func($this->file);
        }

        return $this->file;
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

    public function getContext()
    {
        return $this->context;
    }

    public function getFullDriverPath()
    {
        return $this->getSecretaryManager()->getContextStartingPath($this->getContext()) . '/' . $this->getNewPath();
    }

    public function getNewPath()
    {
        if ($this->newPath !== null) {
            return $this->newPath;
        }

        $uuid = $this->getFileUniqueIdentifier();

        $ext = $this->getFileExtension();

        if ($ext) {
            $ext = '.' . $ext;
        } else {
            $ext = '';
        }

        $contextData = $this->getSecretaryManager()->getConfig("contexts." . $this->getContext());

        if (static::contextCategoryIsForImages($contextData['category'])) {
            if ($ext === null) {
                throw new \InvalidArgumentException("Can not store image without extension.");
            }
            $newPath = $uuid . '/' . $this->getImageName(static::MAIN_IMAGE_NAME . $ext);
        } elseif ($contextData['category'] === ContextCategoryTypes::TYPE_BASIC_FILE) {
            $newPath = $uuid . $ext;
        } else {
            throw new \ErrorException("Context category is not supported.");
        }

        $this->newPath = $newPath;

        return $this->newPath;
    }

    public function getFileUniqueIdentifier()
    {
        if ($this->fileUniqueIdentifier !== null) {
            return $this->fileUniqueIdentifier;
        }

        $generator = $this->getSecretaryManager()->getConfig("file_name_generator");

        $this->fileUniqueIdentifier = forward_static_call([$generator, 'generate'], $this);

        return $this->fileUniqueIdentifier;
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
     * The generic file instance
     *
     * @return File
     */
    public function getFileInstance()
    {
        return $this->getResolvedFile();
    }

    private function getOriginalNameExtension()
    {
        $ext = explode('.', $this->originalName);

        if (empty($ext)) {
            return null;
        }

        return array_pop($ext);
    }

    /**
     * @return MimeDbRepository
     */
    private static function getMimeDb()
    {
        if (null === self::$mimeDb) {
            self::$mimeDb = app(MimeDbRepository::class);
        }

        return self::$mimeDb;
    }

    protected static function contextCategoryIsForImages($category)
    {
        return in_array($category, [ContextCategoryTypes::TYPE_IMAGE, ContextCategoryTypes::TYPE_MANIPULATED_IMAGE]);
    }

    /**
     * @param $default
     * @return string
     */
    protected function getImageName($default)
    {
        $payload = $this->getPayload();

        if ( ! is_array($this->payload)) {
            return $default;
        }

        return array_get($payload, 'image_template_name', $default);
    }

    public function getPayload()
    {
        return $this->payload;
    }

    public function getContextData()
    {
        return $this->getSecretaryManager()->getConfig("contexts." . $this->getContext());
    }

    public function getOriginalName($fallback = true)
    {
        return $this->originalName ?: ($fallback ? $this->getFileName() : null);
    }

    /**
     * Get file name including the extension
     *
     * @param bool $ext
     * @return string
     */
    public function getFileName($ext = true)
    {
        $ext = ($ext ? ($this->getFileExtension() ? ('.' . $this->getFileExtension()) : '') : '');
        $category = $this->getContextData()['category'];

        if (
            $category === ContextCategoryTypes::TYPE_MANIPULATED_IMAGE ||
            $category === ContextCategoryTypes::TYPE_IMAGE
        ) {
            return $this->getImageName(static::MAIN_IMAGE_NAME . $ext);
        }

        return $this->getFileUniqueIdentifier() . $ext;
    }

    public function getSiblingFolder()
    {
        $contextData = $this->getSecretaryManager()->getConfig("contexts." . $this->getContext());

        if (static::contextCategoryIsForImages($contextData['category'])) {
            return $this->getFileUniqueIdentifier();
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

    public function getFileContents()
    {
        $this->getFileInstance();

        if ($this->fileContents instanceof \Closure) {
            $this->fileContents = call_user_func($this->fileContents);
        }

        return $this->fileContents;
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

    public function getPayloadByKey($key)
    {
        return array_get($this->getPayload(), $key);
    }
}