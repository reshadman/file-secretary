<?php

namespace Reshadman\FileSecretary\Infrastructure\Images\Templates;

use Intervention\Image\Constraint;
use Intervention\Image\Image as InterventionImage;
use Intervention\Image\ImageManager;
use Intervention\Image\Imagick\Driver as ImagickDriver;
use Reshadman\FileSecretary\Infrastructure\Images\DynamicTemplateInterface;
use Reshadman\FileSecretary\Infrastructure\Images\TemplateManager;

class DynamicResizableTemplate extends AbstractDynamicTemplate implements DynamicTemplateInterface
{
    public static $driver;

    /**
     * Intervention image instance
     *
     * @param InterventionImage $image
     * @return InterventionImage
     */
    public function makeFromImage(InterventionImage $image)
    {
        // Enable interlace JPEG will be progressive
        $image->interlace(true);
        $format = $image->mime();

        // Check that arguments are set.
        $args = $this->getArgs();
        $this->checkArgs($args);

        if (array_get($args, 'quality') !== null) {
            $image = $image->encode($format, (int)$args['quality']);
        }

        if (isset($args['width']) && isset($args['height']) && array_get($args, 'mode') === TemplateManager::MODE_FIT) {

            $image->fit($args['width'], $args['height']);

        } elseif (isset($args['width'])) {
            // If one of the params are not defined so we will let them be
            // auto generated.
            if ($args['width'] === null || $args['height'] === null) {
                $callback = function (Constraint $con) {
                    $con->aspectRatio();
                };
            } else {
                $callback = null;
            }
            $image = $image->resize($args['width'], $args['height'], $callback);
        } elseif (isset($args['max_width'])) {
            // If max width is defined and it current image is bigger than max width
            // we will resize the image to the max width and auto resize the height,
            // the same process goes for height.
            if ($args['max_width'] !== null) {
                if ($image->getWidth() > $args['max_width']) {
                    $changeWidth = $args['max_width'];
                } else {
                    $changeWidth = null;
                }
            } else {
                $changeWidth = null;
            }

            if ($args['max_height'] !== null) {
                if ($image->getHeight() > $args['max_height']) {
                    $changeHeight = $args['max_height'];
                } else {
                    $changeHeight = null;
                }
            } else {
                $changeHeight = null;
            }
            // Resize only if max_width or max_height exceed.
            if ($changeHeight !== null || $changeWidth !== null) {
                $image->resize($changeWidth, $changeHeight, function (Constraint $con) {
                    $con->aspectRatio();
                });
            }
        } elseif (isset($args['min_width'])) {
            // If min width is defined and it current image is less than min width
            // we will resize the image to the min width and auto resize the height,
            // the same process goes for height.
            if ($args['min_width'] !== null) {
                if ($image->getWidth() < $args['min_width']) {
                    $changeWidth = $args['min_width'];
                } else {
                    $changeWidth = null;
                }
            } else {
                $changeWidth = null;
            }

            if ($args['min_height'] !== null) {
                if ($image->getHeight() < $args['min_height']) {
                    $changeHeight = $args['min_height'];
                } else {
                    $changeHeight = null;
                }
            } else {
                $changeHeight = null;
            }

            // Resize only if min_width or min_height exceed.
            if ($changeHeight !== null || $changeWidth !== null) {
                $image->resize($changeWidth, $changeHeight, function (Constraint $con) {
                    $con->aspectRatio();
                });
            }
        }

        if (array_key_exists('blur', $args)) {
            $image = $this->applyBlurToImage($image, $args['blur']);
        }

        $image = $this->decoratePossibleTransparentPng($image);

        return $image;
    }

    /**
     * If given image is png and it may have transparent background
     * we will generate a canvas(empty image) with given width and height
     * and insert the original png image into that.
     *
     * @param \Intervention\Image\Image $image
     * @return \Intervention\Image\Image
     */
    protected function decoratePossibleTransparentPng(InterventionImage $image)
    {
        if ($image->mime() == 'image/png') {
            $empty = $this->getImageManager()->canvas($image->getWidth(), $image->getHeight());
            $image = $empty->fill($image);
            $image->encode('png');
        }

        return $image;
    }

    /**
     * Get image manager instance
     *
     * @return \Intervention\Image\ImageManager
     */
    protected function getImageManager()
    {
        return isset(static::$driver) ? new ImageManager(['driver' => static::$driver]) : app('image');
    }

    /**
     * Check that arguments are provided.
     *
     * @param $args
     * @return mixed
     */
    protected function checkArgs($args)
    {
        if (
            (!array_key_exists('width', $args) || !array_key_exists('height', $args)) &&
            (!array_key_exists('max_width', $args) || !array_key_exists('max_height', $args)) &&
            (!array_key_exists('min_width', $args) || !array_key_exists('min_height', $args))
        ) {
            throw new \InvalidArgumentException("Width and height are required.");
        }
        return $args;
    }

    /**
     * Apply blur to image
     *
     * @param \Intervention\Image\Image $image
     * @param                           $blur
     * @return \Intervention\Image\Image
     */
    protected function applyBlurToImage(InterventionImage $image, $blur)
    {
        $image->brightness(5);

        if (is_a($image->getDriver(), ImagickDriver::class)) {
            $image->getCore()->blurImage($blur, $blur * 0.5);
        } else {
            $image->blur($blur > 100 ? 100 : $blur);
        }

        return $image;
    }

    public function finalize(InterventionImage $image, $wantedFormat)
    {
        $quality = $this->getQuality();

        $strip = $this->shouldStrip();

        if ($quality === null && !$strip) {
            return parent::finalize($image, $wantedFormat);
        }

        $this->checkExtension($image, $wantedFormat);

        if ($strip && $image->getDriver() instanceof ImagickDriver) {
            $image->getCore()->stripImage();
        }

        if ($quality === null) {
            return $image->encode($wantedFormat);
        }

        return $image->encode($wantedFormat, $quality);
    }

    /**
     * @return null|int
     */
    public function getQuality()
    {
        return array_get($this->args, 'quality', null);
    }

    protected function shouldStrip()
    {
        return array_get($this->args, 'strip', false);
    }
}