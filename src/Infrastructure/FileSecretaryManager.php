<?php

namespace Reshadman\FileSecretary\Infrastructure;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Support\Str;
use Reshadman\FileSecretary\Application\PersistableFile;

class FileSecretaryManager
{
    private $config;
    /**
     * @var FilesystemManager
     */
    private $filesystemManager;

    public function __construct(array $config, FilesystemManager $filesystemManager)
    {
        $this->config = $config;
        $this->filesystemManager = $filesystemManager;
    }

    /**
     * @param $context
     * @return FilesystemAdapter|mixed
     */
    public function getContextDriver($context)
    {
        return $this->filesystemManager->disk($this->getConfig('contexts.' . $context . '.driver'));
    }

    public function getContextStartingPath($context)
    {
        return $this->getConfig("contexts.{$context}.context_folder");
    }

    public function getAssetStartingPath($context, $assetTag)
    {
        return $this->getContextStartingPath($context) . '/' . $assetTag;
    }

    public function getConfig($key = null, $def = null)
    {
        if ($key === null) {
            return $this->config;
        }
        return array_get($this->config, $key, $def);
    }


    public function scan($dir, $all = [])
    {
        $dir = rtrim($dir, '/');

        foreach (scandir($dir) as $key => $content) {

            $path = $dir . '/' . $content;

            if ( ! Str::startsWith($content, '.')) {

                if (is_file($path) && is_readable($path)) {

                    $all[] = $path;

                } elseif (is_dir($path) && is_readable($path)) {

                    $all = static::scan($path, $all);

                }
            }
        }

        return $all;
    }

    public function replaceFirst($from, $to, $subject)
    {
        $from = '/' . preg_quote($from, '/') . '/';

        return preg_replace($from, $to, $subject, 1);
    }

    public function getContextData($context)
    {
        return $this->getConfig("contexts.{$context}");
    }

    public function getMimeForExtension($ext)
    {
        return app(MimeDbRepository::class)->findType($ext);
    }

    public static function normalizeExtension($ext)
    {
        $map = [
            'jpeg' => 'jpg'
        ];

        if (array_key_exists($ext, $map)) {
            return $map[$ext];
        }

        return $ext;
    }

    /**
     * @return Model|PersistableFile
     */
    public function getPersistModel()
    {
        $model = $this->getConfig("eloquent.model");

        if ($model === null) {
            throw new \InvalidArgumentException("Model Can not be null");
        }

        if (!$model instanceof Model || !$model instanceof PersistableFile) {
            throw new \InvalidArgumentException("Model is not valid.");
        }

        return app($model);
    }
}