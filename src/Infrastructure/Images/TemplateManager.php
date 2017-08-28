<?php

namespace Reshadman\FileSecretary\Infrastructure\Images;

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
        $templates = $this->getAvailableTemplates();

        // If template is not in out array list
        // we will throw an exception.
        if ( ! $this->templateExists($templateName)) {
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

        if ( ! isset($template['class'])) {
            throw new \InvalidArgumentException('No [class] key given for template meta.');
        }

        $instance = new $template['class'];
        if ( ! $instance instanceof DynamicTemplateInterface) {
            return $instance;
        }

        // If there is no args given we will throw an exception.
        if ( ! isset($template['args']) || ! is_array($template['args'])) {
            throw new \InvalidArgumentException("You have not set [args] key for dynamic template array.");
        }

        $instance->setArgs($template['args']);

        return $instance;
    }

    public function getAvailableTemplates()
    {
        return $this->availableTemplates;
    }

    public function setAvailableTemplates(array $templates)
    {
        $this->availableTemplates = $templates;
    }

    public function templateExists($template)
    {
        return array_key_exists($template, $this->getAvailableTemplates());
    }

    public function appendToAvailableTemplates(array $template)
    {
        $this->availableTemplates[] = $template;
        return $this;
    }
}
