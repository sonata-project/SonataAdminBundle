<?php

namespace Sonata\AdminBundle\Tests\Fixtures\Generator;

use Sonata\AdminBundle\Generator\AbstractBcGenerator;

class GeneratorBc22 extends AbstractBcGenerator
{
    protected function render($skeletonDir, $template, $parameters)
    {
        if ($skeletonDir === array('path/to/templates') && $template === 'test.html.twig' && $parameters === array('foo' => 'bar')) {
            return 'Result OK';
        }

        return 'Result invalid';
    }

    protected function renderFile($skeletonDir, $template, $target, $parameters)
    {
        if ($this->render($skeletonDir, $template, $parameters) === 'Result OK' && $target === 'target_file') {
            return true;
        }

        return false;
    }
}
