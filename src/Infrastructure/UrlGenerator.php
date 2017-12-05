<?php

namespace Reshadman\FileSecretary\Infrastructure;

use Reshadman\FileSecretary\Application\AddressableRemoteFile;
use Reshadman\FileSecretary\Application\ContextCategoryTypes;
use Reshadman\FileSecretary\Application\PersistableFile;

class UrlGenerator
{
    /** @var  FileSecretaryManager */
    private static $manager;
    private static $cache = [];

    /**
     * @param FileSecretaryManager $secretaryManager
     */
    public static function setSecretaryManager(FileSecretaryManager $secretaryManager)
    {
        static::$manager = $secretaryManager;
    }

    /**
     * @return FileSecretaryManager
     */
    public static function getManager()
    {
        if (static::$manager === null) {
            static::setSecretaryManager(app(FileSecretaryManager::class));
        }

        return static::$manager;
    }

    /**
     * On Config Change we purge our cache
     */
    public static function purgeCalculated()
    {
        static::$cache = [];
    }

    /**
     * This generates the url for asset,
     *
     *
     * @param $assetFolder
     * @param $item
     * @param bool $force
     * @return string
     */
    public static function asset($assetFolder, $item, $force = false)
    {
        $cacheKey = 'asset_folders__' . $assetFolder . '_bool_' . (string)$force;

        if ( ! array_key_exists($cacheKey, static::$cache)) {
            $baseAddress = static::getManager()->assetFolderToStartingUrl($assetFolder, $force);
            static::$cache[$cacheKey] = $baseAddress;
        }

        return rtrim(static::$cache[$cacheKey], '/') . '/' . trim($item, '/');
    }

    /**
     * @param $context
     * @param $fullRelative
     * @param array $appending
     * @return null|string
     */
    public static function fromContextFullRelative($context, $fullRelative, $appending = [])
    {
        $cacheKey = 'full_relative_to_full_url__' . $context;

        if ( ! array_key_exists($cacheKey, static::$cache)) {
            $baseAddress = static::getManager()->getConfig("contexts.$context.driver_base_address");

            if ($baseAddress === null) {
                return null;
            }

            static::$cache[$cacheKey] = $baseAddress;
        }

        $url = rtrim(static::$cache[$cacheKey], '/') . '/' . trim($fullRelative);

        if (empty($appending)) {
            return $url;
        }

        $queryPart = http_build_query($appending);

        return http_build_query($url, ['query' => $queryPart]);
    }

    /**
     * @param $contextName
     * @param $contextFolder
     * @param $afterContextPath
     * @param bool $preferBaseAddress
     * @param array $appending
     * @return null|string
     */
    public static function fromContextSpec(
        $contextName,
        $contextFolder,
        $afterContextPath,
        $preferBaseAddress = true,
        $appending = []
    ) {
        if ($preferBaseAddress) {
            $contextBaseAddress = static::getManager()->getConfig("contexts.$contextName.driver_base_address");


            if ($contextBaseAddress) {
                return self::fromContextFullRelative($contextName, $contextFolder . '/' . $afterContextPath, $appending);
            }
        }

        return route('file-secretary.get.download_file', array_merge([
            'context_name' => $contextName,
            'context_folder' => $contextFolder,
            'after_context_path' => $afterContextPath
        ], $appending));
    }

    /**
     * Get address for an eloquent instance
     *
     * @param PersistableFile $persistedFile
     * @param bool $preferBase
     * @param array $appending
     * @return array|null|string
     */
    public static function fromEloquentInstance(PersistableFile $persistedFile, $preferBase = true, $appending = [])
    {
        $sibling = $persistedFile->getFileableSiblingFolder();

        if ($sibling !== null) {
            $path = $sibling . '/' . $persistedFile->getFileableFullFileName();
        } else {
            $path = $persistedFile->getFileableFullFileName();
        }

        return static::fromContextSpec(
            $persistedFile->getFileableContext(),
            $persistedFile->getFileableContextFolder(),
            $path,
            $preferBase,
            $appending
        );
    }

    public static function getImagesTemplatesForEloquentInstance(
        PersistableFile $persistedFile,
        $preferBase = true,
        $appending = []
    ) {
        return static::getImageTemplatesFromContextSpec(
            $persistedFile->getFileableContext(),
            $persistedFile->getFileableContextFolder(),
            $persistedFile->getFileableSiblingFolder(),
            $persistedFile->getFileableFileName(),
            $persistedFile->getFileableExtension(),
            $preferBase,
            $appending
        );
    }

    public static function fromAddressableRemoteFile(AddressableRemoteFile $remoteFile, $preferBase = true, $appending = [])
    {
        return static::fromContextSpec(
            $remoteFile->getContextName(),
            $remoteFile->getContextFolder(),
            $remoteFile->relative(),
            $preferBase,
            $appending
        );
    }

    public static function getImageTemplatesForRemoteFile(AddressableRemoteFile $remoteFile, $preferBase = true, $appending = [])
    {
        $contextData = static::getManager()->getContextData($remoteFile->getContextName());

        if ( ! ContextCategoryTypes::isImageCategory($contextData['category'])) {
            return null;
        }

        $relative = $remoteFile->relative();

        $sibling = trim(pathinfo($relative, PATHINFO_DIRNAME), DIRECTORY_SEPARATOR);
        $fileName = pathinfo($relative, PATHINFO_FILENAME);
        $extension = pathinfo($relative, PATHINFO_EXTENSION);

        return static::getImageTemplatesFromContextSpec(
            $remoteFile->getContextName(),
            $contextData['context_folder'],
            $sibling,
            $fileName,
            $extension === '' || $extension === false ? null : $extension,
            $preferBase,
            $appending
        );
    }

    public static function getImageTemplatesFromContextSpec(
        $contextName,
        $contextFolder,
        $sibling,
        $parentFileName,
        $parentFileExtension,
        $preferBase = true,
        $appending = []
    ) {
        $contextData = static::getManager()->getContextData($contextName);

        // If the file is not image we will simply return
        if ($contextData['category'] !== ContextCategoryTypes::TYPE_IMAGE) {
            return null;
        }

        $parent = static::fromContextSpec(
            $contextName,
            $contextFolder,
            $sibling . '/' . $parentFileName . ($parentFileExtension ? '.' . $parentFileExtension : ''),
            $preferBase,
            $appending
        );

        if (array_key_exists('allowed_templates', $contextData)) {
            $templates = $contextData['allowed_templates'];
        } else {
            $templates = static::getManager()->getAvailableTemplates();
        }

        $data = [
            'parent_image_url' => $parent,
            'parent_extension' => $parentFileExtension
        ];

        $children = [];
        $childrenContext = null;
        $siblingFolder = $sibling;
        $childrenContextName = is_string($contextData['store_manipulated']) ? $contextData['store_manipulated'] : $contextName;
        foreach ($templates as $templateName => $template) {
            if ($childrenContext === null) {
                if (is_string($contextData['store_manipulated'])) {
                    $childrenContext = static::getManager()->getContextData($contextData['store_manipulated']);
                } else {
                    $childrenContext = $contextData;
                }
            }

            $encodings = array_get($template, 'args.encodings', null);

            foreach ((array)$encodings as $encoding) {
                $children[$templateName . '.' . $encoding] = static::fromContextSpec(
                    $childrenContextName,
                    $childrenContext['context_folder'],
                    $siblingFolder . '/' . $templateName . '.' . $encoding,
                    $preferBase,
                    $appending
                );
            }

            if ($encodings === null) {
                $children[$templateName] = static::fromContextSpec(
                    $childrenContextName,
                    $childrenContext['context_folder'],
                    $siblingFolder . '/' . $templateName . '.' . $parentFileExtension,
                    $preferBase,
                    $appending
                );
            }
        }

        $data['children'] = $children;
        return $data;
    }
}