<?php

namespace Reshadman\FileSecretary\Infrastructure\Images;

interface DynamicTemplateInterface extends TemplateInterface
{
    public function setArgs(array $args = []);
}