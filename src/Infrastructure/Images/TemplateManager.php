<?php

namespace Reshadman\FileSecretary\Infrastructure\Images;

use Reshadman\FileSecretary\Infrastructure\Images\Templates\DynamicResizableTemplate;

class TemplateManager
{
    const MODE_FIT = 0;

    /**
     * Available Templates to resize an image
     *
     * @var array
     */
    protected $availableTemplates = [];

    /**
     * TemplateManager constructor.
     * @param array $availableTemplates
     */
    public function __construct(array $availableTemplates)
    {
        $this->setAvailableTemplates($availableTemplates);
    }

    /**
     * Get template instance
     *
     * @param $templateName
     * @return DynamicTemplateInterface|TemplateInterface
     */
    public function getTemplateInstance($templateName)
    {
        $templates = static::getTemplatesMeta();

        // If template is not in out array list
        // we will throw an exception.
        if (!$this->templateExists($templateName)) {
            throw new \InvalidArgumentException('No template with given name provided.');
        }

        // Check that template has args
        // or is an instance of dynamic templates
        // dynamic templates can must have arguments which then decide based on
        // them what to create.
        $template = $templates[$templateName];
        if (is_string($template)) {
            $instance = new $template;
            if ($instance instanceof DynamicTemplateInterface) {
                throw new \InvalidArgumentException('Template: ' . $template . ' is a dynamic template and needs args but nothing given.');
            }
            return $instance;
        }

        if (!isset($template['class'])) {
            throw new \InvalidArgumentException('No [class] key given for template meta.');
        }

        $instance = new $template['class'];
        if (!$instance instanceof DynamicTemplateInterface) {
            return $instance;
        }

        // If there is no args given we will throw an exception.
        if (!isset($template['args']) || !is_array($template['args'])) {
            throw new \InvalidArgumentException("You have not set [args] key for dynamic template array.");
        }

        $instance->setArgs($template['args']);

        return $instance;
    }

    /**
     * Get templates meta data
     *
     * @return array
     */
    protected static function getTemplatesMeta()
    {
        return static::$templates ?: static::$templates = static::generateTemplatesMeta();
    }

    /**
     * Get collections and their available meta data.
     *
     * @return array
     */
    public static function getCollections()
    {
        return [
            'companies_logo' => [
                'companies_logo_blurred_500x220',
                'companies_logo_300_min',
                'companies_logo_200x200',
                'companies_logo_64x64',
                'companies_logo_128x128',
                'companies_logo_300xauto',
                'companies_logo_800xauto',
                'companies_logo_48x48',
                'companies_logo_200x200_75',
                'companies_logo_128x128_75',
                'companies_logo_64x64_75',
                'companies_logo_48x48_75',
                'companies_logo_200x200_strip_75',
                'companies_logo_128x128_strip_75',
                'companies_logo_64x64_strip_75',
                'companies_logo_48x48_strip_75'
            ],
            'companies_cover' => [
                'companies_cover_640xauto',
                'companies_cover_1920xauto'
            ],
            'companies_media' => [
                'companies_media_490xauto',
                'companies_media_690xauto',
                'companies_media_768xauto',
                'companies_media_320x420',
                'companies_media_730x958',
                'companies_media_768xauto_strip_75'
            ],
            'companies_avatar' => [
                'companies_avatar_100x100',
                'companies_avatar_200x200'
            ],
            'anetwork_banner' => [
                'anetwork_banner_350xauto'
            ],
            'job_category_image' => [
                'job_category_150x150_fit'
            ],
            'cms' => [
                'cms_thumbnail_120x90',
                'cms_thumbnail_850x425',
                'cms_thumbnail_294x168'
            ],
            'cv_avatar' => [
                'cv_avatar_128x128',
                'cv_avatar_256x256',
                'cv_avatar_512x512'
            ]
        ];
    }

    /**
     * Generate templates meta.
     *
     * @return array
     */
    public static function generateTemplatesMeta()
    {
        return [
            'companies_logo_200x200' => [
                'class' => DynamicResizableTemplate::class,
                'args' => ['width' => 200, 'height' => 200, 'encodings' => ['png', 'jpg']],
            ],
            'companies_logo_64x64' => [
                'class' => DynamicResizableTemplate::class,
                'args' => ['width' => 64, 'height' => 64, 'encodings' => ['png', 'jpg']],
            ],
            'companies_logo_128x128' => [
                'class' => DynamicResizableTemplate::class,
                'args' => ['width' => 128, 'height' => 128, 'encodings' => ['png', 'jpg']],
            ],
            'companies_logo_300xauto' => [
                'class' => DynamicResizableTemplate::class,
                'args' => ['width' => 300, 'height' => null],
            ],
            'companies_cover_640xauto' => [
                'class' => DynamicResizableTemplate::class,
                'args' => ['width' => 640, 'height' => null, 'encodings' => ['jpg']],
            ],
            'companies_cover_1920xauto' => [
                'class' => DynamicResizableTemplate::class,
                'args' => ['max_width' => 1920, 'max_height' => null, 'encodings' => ['jpg']],
            ],
            'companies_media_490xauto' => [
                'class' => DynamicResizableTemplate::class,
                'args' => ['width' => 490, 'height' => null, 'encodings' => ['jpg']],
            ],
            'companies_media_690xauto' => [
                'class' => DynamicResizableTemplate::class,
                'args' => ['width' => 690, 'height' => null, 'encodings' => ['jpg']],
            ],
            'companies_media_768xauto' => [
                'class' => DynamicResizableTemplate::class,
                'args' => ['width' => 768, 'height' => null, 'encodings' => ['jpg']],
            ],
            'companies_avatar_100x100' => [
                'class' => DynamicResizableTemplate::class,
                'args' => ['width' => 100, 'height' => 100, 'encodings' => ['jpg']],
            ],
            'companies_media_320x420' => [
                'class' => DynamicResizableTemplate::class,
                'args' => ['width' => 320, 'height' => 420, 'encodings' => ['jpg']],
            ],
            'companies_media_730x958' => [
                'class' => DynamicResizableTemplate::class,
                'args' => ['width' => 730, 'height' => 958, 'encodings' => ['jpg']],
            ],
            'anetwork_banner_350xauto' => [
                'class' => DynamicResizableTemplate::class,
                'args' => ['width' => '350', 'height' => null],
            ],
            'companies_logo_800xauto' => [
                'class' => DynamicResizableTemplate::class,
                'args' => ['width' => 800, 'height' => null],
            ],
            'companies_logo_48x48' => [
                'class' => DynamicResizableTemplate::class,
                'args' => ['width' => 32, 'height' => 32],
            ],
            'companies_logo_300_min' => [
                'class' => DynamicResizableTemplate::class,
                'args' => ['min_width' => 300, 'min_height' => 300],
            ],
            'companies_logo_blurred_500x220' => [
                'class' => DynamicResizableTemplate::class,
                'args' => ['blur' => 140, 'width' => 500, 'height' => 220],
            ],
            'job_category_150x150_fit' => [
                'class' => DynamicResizableTemplate::class,
                'args' => ['width' => 150, 'height' => 150, 'mode' => static::MODE_FIT],
            ],
            'cms_thumbnail_120x90' => [
                'class' => DynamicResizableTemplate::class,
                'args' => ['width' => 120, 'height' => 90, 'mode' => static::MODE_FIT],
            ],
            'cms_thumbnail_294x168' => [
                'class' => DynamicResizableTemplate::class,
                'args' => ['width' => 294, 'height' => 168, 'mode' => static::MODE_FIT],
            ],
            'cms_thumbnail_850x425' => [
                'class' => DynamicResizableTemplate::class,
                'args' => ['width' => 850, 'height' => 425],
            ],
            'companies_logo_200x200_75' => [
                'class' => DynamicResizableTemplate::class,
                'args' => ['width' => 200, 'height' => 200, 'encodings' => ['png', 'jpg'], 'quality' => 75],
            ],
            'companies_logo_128x128_75' => [
                'class' => DynamicResizableTemplate::class,
                'args' => ['width' => 128, 'height' => 128, 'encodings' => ['png', 'jpg'], 'quality' => 75],
            ],
            'companies_logo_64x64_75' => [
                'class' => DynamicResizableTemplate::class,
                'args' => ['width' => 64, 'height' => 64, 'encodings' => ['png', 'jpg'], 'quality' => 75],
            ],
            'companies_logo_48x48_75' => [
                'class' => DynamicResizableTemplate::class,
                'args' => ['width' => 48, 'height' => 48, 'encodings' => ['png', 'jpg'], 'quality' => 75],
            ],

            'companies_logo_200x200_strip_75' => [
                'class' => DynamicResizableTemplate::class,
                'args' => ['width' => 200, 'height' => 200, 'encodings' => ['png', 'jpg'], 'strip' => true, 'quality' => 75],
            ],
            'companies_logo_128x128_strip_75' => [
                'class' => DynamicResizableTemplate::class,
                'args' => ['width' => 128, 'height' => 128, 'encodings' => ['png', 'jpg'], 'strip' => true, 'quality' => 75],
            ],
            'companies_logo_64x64_strip_75' => [
                'class' => DynamicResizableTemplate::class,
                'args' => ['width' => 64, 'height' => 64, 'encodings' => ['png', 'jpg'], 'strip' => true, 'quality' => 75],
            ],
            'companies_logo_48x48_strip_75' => [
                'class' => DynamicResizableTemplate::class,
                'args' => ['width' => 48, 'height' => 48, 'encodings' => ['png', 'jpg'], 'strip' => true, 'quality' => 75],
            ],

            'companies_media_768xauto_strip_75' => [
                'class' => DynamicResizableTemplate::class,
                'args' => ['width' => 768, 'height' => null, 'encodings' => ['jpg'], 'strip' => true, 'quality' => 75],
            ],


            'cv_avatar_128x128' => [
                'class' => DynamicResizableTemplate::class,
                'args' => ['width' => 128, 'height' => 128, 'encodings' => ['png', 'jpg'], 'strip' => true, 'mode' => static::MODE_FIT],
            ],
            'cv_avatar_256x256' => [
                'class' => DynamicResizableTemplate::class,
                'args' => ['width' => 256, 'height' => 256, 'encodings' => ['png', 'jpg'], 'strip' => true, 'mode' => static::MODE_FIT],
            ],

            'cv_avatar_512x512' => [
                'class' => DynamicResizableTemplate::class,
                'args' => ['width' => 512, 'height' => 512, 'encodings' => ['jpg'], 'strip' => true, 'mode' => static::MODE_FIT],
            ],
        ];
    }

    public function getAvailableTemplates()
    {
        return $this->availableTemplates;
    }

    public function appendToAvailableTemplates(array $template)
    {
        $this->availableTemplates[] = $template;
        return $this;
    }

    public function setAvailableTemplates(array $templates)
    {
        $this->availableTemplates = $templates;
    }

    public function templateExists($template)
    {
        return array_key_exists($template, $this->getAvailableTemplates());
    }
}
