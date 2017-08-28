<?php

namespace Reshadman\FileSecretary\Infrastructure;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Filesystem\FilesystemManager;
use Reshadman\FileSecretary\Application\ContextCategoryTypes;
use Reshadman\FileSecretary\Application\PersistableFile;

class FileSecretaryManager
{
    /**
     * @var array
     */
    private $config = [];

    /**
     * @var FilesystemManager
     */
    private $filesystemManager;

    /**
     * FileSecretaryManager constructor.
     * @param array $config
     * @param FilesystemManager $filesystemManager
     */
    public function __construct(array $config, FilesystemManager $filesystemManager)
    {
        $this->reInitConfig($config);
        $this->filesystemManager = $filesystemManager;
    }

    /**
     * Reinitialize the config.
     *
     * @param array $config
     */
    public function reInitConfig(array $config)
    {
        $this->config = $this->decorateConfig($config);
    }

    /**
     * Test and decorate the config.
     *
     * @param array $config
     * @return array
     */
    protected function decorateConfig(array $config)
    {
        if ( ! array_key_exists('file_name_generator', $config)) {
            throw new \InvalidArgumentException(
                "The config lacks the generator function."
            );
        }

        if ( ! $config['file_name_generator'] instanceof \Closure) {
            throw new \InvalidArgumentException(
                "The file name generator is not a callable function."
            );
        }

        if ( ! array_key_exists("contexts", $config)) {
            throw new \InvalidArgumentException("The 'context' element of the config is not defined.");
        }

        if ( ! is_array($config['contexts'])) {
            throw new \InvalidArgumentException("The 'context' element of the config should be an array");
        }

        $contextUniqueKeys = [];
        $usedManipulated = [];
        foreach ($config['contexts'] as $contextName => $context) {

            if (is_integer($contextName)) {
                throw new \InvalidArgumentException("The context name can not be an integer.");
            }

            $contextFolder = array_get($context, "context_folder", '');

            if ( ! is_string($contextFolder)) {
                throw new \InvalidArgumentException("The context folder should be an string or null");
            }

            $driver = array_get($context, "driver");

            if ( ! is_string($driver)) {
                throw new \InvalidArgumentException("Driver name must be string");
            }

            $driverBaseAddress = array_get($context, "driver_base_address", '');

            if ( ! is_string($driverBaseAddress) && $driverBaseAddress !== null) {
                throw new \InvalidArgumentException("Driver base address should be string.");
            }

            $category = array_get($context, "category");

            if ( ! ContextCategoryTypes::exists($category)) {
                throw new \InvalidArgumentException("Invalid category given.");
            }

            $key = trim($contextFolder) . $driver;
            if (array_key_exists($key, $contextUniqueKeys)) {
                throw new \InvalidArgumentException("Another context with the same driver and folder exists.");
            }

            if ($context['category'] === ContextCategoryTypes::TYPE_IMAGE) {
                $manipulated = array_get($context, 'store_manipulated');

                if (is_string($manipulated)) {
                    if ( ! array_key_exists($manipulated, $config['contexts'])) {
                        throw new \InvalidArgumentException(
                            "The given context for manipulated images is not in the contexts section of the config"
                        );
                    }

                    if (array_key_exists($manipulated, $usedManipulated)) {
                        throw new \InvalidArgumentException(
                            "The given manipulated image context has been used somewhere else"
                        );
                    }

                    $config['contexts'][$manipulated]['category'] = ContextCategoryTypes::TYPE_MANIPULATED_IMAGE;

                    $usedManipulated[$manipulated] = true;
                }
            } elseif ($context['category'] === ContextCategoryTypes::TYPE_ASSET) {
                if (empty($driverBaseAddress)) {
                    throw new \InvalidArgumentException("When using asset context a driver_base_address is mandatory.");
                }
            }

            $contextUniqueKeys[$key] = true;
        }

        $assetFolders = array_get($config, "asset_folders");

        if ($assetFolders === null) {
            $config['asset_folders'] = [];
        }

        if ( ! is_array($config['asset_folders'])) {
            throw new \InvalidArgumentException("The asset_folder element of config should be an assoc array.");
        }

        foreach ($config['asset_folders'] as $name => &$folder) {
            if ( ! array_key_exists('context', $folder)) {
                throw new \InvalidArgumentException("A context is needed for an asset folder.");
            }

            if (
                ! array_key_exists($folder['context'], $config['contexts']) ||
                $config['contexts'][$folder['context']]['category'] !== ContextCategoryTypes::TYPE_ASSET
            ) {
                throw new \InvalidArgumentException(
                    "The given context is not available or the category is not for assets."
                );
            }

            if ( ! array_key_exists('path', $folder) || ! is_string($folder['path'])) {
                throw new \InvalidArgumentException("You should provide the path for the folder.");
            }

            if ( ! array_key_exists('env_key', $folder)) {
                throw new \InvalidArgumentException("A unique env key is needed for this folder.");
            }
        }

        return $config;
    }

    /**
     * We prefer some sibling extensions over the others.
     *
     * @param $ext
     * @return string
     */
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
     * Get context illuminate driver.
     *
     * @param $context
     * @return FilesystemAdapter|mixed
     */
    public function getContextDriver($context)
    {
        return $this->filesystemManager->disk($this->getConfig('contexts.' . $context . '.driver'));
    }

    /**
     * Get config by dot notation.
     *
     * @param null $key
     * @param null $def
     * @return array|mixed
     */
    public function getConfig($key = null, $def = null)
    {
        if ($key === null) {
            return $this->config;
        }
        return array_get($this->config, $key, $def);
    }

    /**
     * Get asset starting path. which appends the asset tag(folder)
     * to the asset's context starting path.
     *
     * @param $context
     * @param $assetTag
     * @return string
     */
    public function getAssetStartingPath($context, $assetTag)
    {
        return $this->getContextStartingPath($context) . '/' . $assetTag;
    }

    /**
     * Get context starting relative path.
     *
     * @param $context
     * @return array|mixed
     */
    public function getContextStartingPath($context)
    {
        return $this->getConfig("contexts.{$context}.context_folder");
    }

    /**
     * Replace the first occurrence of a substring with
     * the given one in the subject string.
     *
     * @param $from
     * @param $to
     * @param $subject
     * @return string
     */
    public function replaceFirst($from, $to, $subject)
    {
        $from = '/' . preg_quote($from, '/') . '/';

        return preg_replace($from, $to, $subject, 1);
    }

    /**
     * Get context array meta data.
     *
     * @param $context
     * @return array
     */
    public function getContextData($context)
    {
        return $this->getConfig("contexts.{$context}");
    }

    /**
     * Get mimetype for the given extension
     *
     * @param $ext
     * @return string|null
     */
    public function getMimeForExtension($ext)
    {
        return app(MimeDbRepository::class)->findType($ext);
    }

    /**
     * Get persistence eloquent model.
     *
     * @return Model|PersistableFile
     */
    public function getPersistModel()
    {
        $model = $this->getConfig("eloquent.model");

        if ($model === null) {
            throw new \InvalidArgumentException("Model Can not be null");
        }

        $model = app($model);

        if ( ! is_a($model, Model::class) || ! is_a($model, PersistableFile::class)) {
            throw new \InvalidArgumentException("Model is not valid.");
        }

        return $model;
    }
}