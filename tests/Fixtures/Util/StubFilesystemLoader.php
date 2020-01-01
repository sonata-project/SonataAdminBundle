<?php

namespace Sonata\AdminBundle\Tests\Fixtures\Util;

use Twig\Loader\FilesystemLoader;

class StubFilesystemLoader extends FilesystemLoader
{
    protected function findTemplate($name, $throw = true)
    {
        // strip away bundle name
        $parts = explode(':', $name);
        return parent::findTemplate(end($parts), $throw);
    }
}
