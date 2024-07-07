<?php

namespace Sonata\AdminBundle\Templating\LayoutStorage;

interface LayoutStorageInterface
{
    public function get(): string;

    public function set(string $layout): void;
}
