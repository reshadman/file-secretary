<?php

namespace Reshadman\FileSecretary\Application;

class ContextCategoryTypes
{
    const TYPE_BASIC_FILE = 'basic_file';
    const TYPE_IMAGE = 'image';
    const TYPE_ASSET = 'asset';
    const TYPE_MANIPULATED_IMAGE = 'manipulated_image';

    public static function getAll()
    {
        return [
            static::TYPE_MANIPULATED_IMAGE,
            static::TYPE_IMAGE,
            static::TYPE_BASIC_FILE,
            static::TYPE_ASSET
        ];
    }

    public static function exists($category)
    {
        return in_array($category, static::getAll());
    }
}