<?php

namespace Reshadman\FileSecretary\Infrastructure\Images\Templates;

use Intervention\Image\Image;
use Reshadman\FileSecretary\Infrastructure\Images\DynamicTemplateInterface;
use Reshadman\FileSecretary\Infrastructure\Images\FileSecretaryImageManager;
use Reshadman\FileSecretary\Infrastructure\MimeDbRepository;

abstract class AbstractDynamicTemplate implements DynamicTemplateInterface
{
    protected $args = [];

    public function finalize(Image $image, $wantedFormat)
    {
        $this->checkExtension($image, $wantedFormat);
        return $image->isEncoded() ? $image->getEncoded() : $image->encode($wantedFormat)->getEncoded();
    }

    protected function checkExtension(Image $image, $extension)
    {
        if ($extension === null) {
            throw new \InvalidArgumentException("No Image Extension Given.");
        }

        $availableExtensions = $this->getArg('encodings');

        if ($availableExtensions === null) {
            $current = $this->getImageExtension($image);

            if ( ! FileSecretaryImageManager::extensionsAreEqual($current, $extension)) {
                throw new \InvalidArgumentException("There is no encodings defined for the template and also the given extension is not equal to the main file extension.");
            }

            return;
        }

        if ( ! in_array($extension, $availableExtensions)) {
            throw new \InvalidArgumentException("Given extension is not supported.");
        }
    }

    public function getArg($key, $force = false)
    {
        $args = $this->getArgs();

        if ($force && ! isset($args[$key])) {
            throw new \LogicException("Arg : {$key} not given.");
        }

        return array_get($args, $key);
    }

    public function getArgs()
    {
        return $this->args;
    }

    public function setArgs(array $args = [])
    {
        $this->args = $args;
        return $this;
    }

    /**
     * @param Image $image
     * @return null|string
     */
    protected function getImageExtension(Image $image)
    {
        $db = app(MimeDbRepository::class);
        $current = $db->findExtension($image->mime());
        return $current;
    }
}