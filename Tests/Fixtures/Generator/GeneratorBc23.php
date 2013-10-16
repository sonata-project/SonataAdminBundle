<?php

namespace Sonata\AdminBundle\Tests\Fixtures\Generator;

use Sonata\AdminBundle\Generator\AbstractBcGenerator;

class GeneratorBc23 extends AbstractBcGenerator
{
    private $skeletonDirs;

    public function setSkeletonDirs($skeletonDirs)
    {
        $this->skeletonDirs = array($skeletonDirs);
    }

    protected function render($template, $parameters)
    {
        if ($this->skeletonDirs === array('path/to/templates') && $template === 'test.html.twig' && $parameters === array('foo' => 'bar')) {
            return 'Result OK';
        }

        return 'Result invalid';
    }

    protected function renderFile($template, $target, $parameters)
    {
        if ($this->render($template, $parameters) === 'Result OK' && $target === 'target_file') {
            return true;
        }

        return false;
    }
}
