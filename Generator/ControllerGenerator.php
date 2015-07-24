<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Generator;

use Sensio\Bundle\GeneratorBundle\Generator\Generator;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

/**
 * @author Marek Stipek <mario.dweller@seznam.cz>
 * @author Simon Cosandey <simon.cosandey@simseo.ch>
 */
class ControllerGenerator extends Generator
{
    /** @var string|null */
    private $class;

    /** @var string|null */
    private $file;

    /**
     * @param array|string $skeletonDirectory
     */
    public function __construct($skeletonDirectory)
    {
        $this->setSkeletonDirs($skeletonDirectory);
    }

    /**
     * @param BundleInterface $bundle
     * @param string          $controllerClassBasename
     *
     * @throws \RuntimeException
     */
    public function generate(BundleInterface $bundle, $controllerClassBasename)
    {
        $this->class = sprintf('%s\Controller\%s', $bundle->getNamespace(), $controllerClassBasename);
        $this->file = sprintf(
            '%s/Controller/%s.php',
            $bundle->getPath(),
            str_replace('\\', '/', $controllerClassBasename)
        );
        $parts = explode('\\', $this->class);

        if (file_exists($this->file)) {
            throw new \RuntimeException(sprintf(
                'Unable to generate the admin controller class "%s". The file "%s" already exists.',
                $this->class,
                realpath($this->file)
            ));
        }

        $this->renderFile('AdminController.php.twig', $this->file, array(
            'classBasename' => array_pop($parts),
            'namespace'     => implode('\\', $parts),
        ));
    }

    /**
     * @return string|null
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return string|null
     */
    public function getFile()
    {
        return $this->file;
    }
}
