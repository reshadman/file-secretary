<?php

namespace Reshadman\FileSecretary\Application;

interface StoreConfigGeneratorInterface
{
    public function generateForContext($contextName, $contextData);
}