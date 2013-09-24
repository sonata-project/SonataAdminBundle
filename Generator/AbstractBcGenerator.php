<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Sonata\AdminBundle\Generator;

use Sensio\Bundle\GeneratorBundle\Generator\Generator;

/**
 * Class that fixes backward incompatible changes between Sensio Generator 2.2 and 2.3.
 * This class should be removed if support for Symfony 2.2 (and Sensio Generator 2.2) will be dropped.
 *
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
abstract class AbstractBcGenerator extends Generator
{
    /**
     * @var array
     */
    private $skeletonDirs;

    /**
     * @var boolean
     */
    private $bcEnabled = false;

    /**
     * Returns the Generator Version
     *
     * @return string
     */
    public static function getGeneratorVersion()
    {
        $r = new \ReflectionClass('Sensio\Bundle\GeneratorBundle\Generator\Generator');

        return $r->hasMethod('setSkeletonDirs') === true ? '2.3' : '2.2';
    }

    /**
     * {@inheritdoc}
     */
    public function setSkeletonDirs($skeletonDirs)
    {
        $this->skeletonDirs = is_array($skeletonDirs) ? $skeletonDirs : array($skeletonDirs);

        $this->bcEnabled = false;

        if (self::getGeneratorVersion() === '2.2') {
            $this->bcEnabled = true;
        }

        if (self::getGeneratorVersion() === '2.3') {
            parent::setSkeletonDirs($this->skeletonDirs);
        }
    }

    /**
     * Set backward compatibility with Sensio Generator 2.2.*
     *
     * @param boolean $bcEnabled
     */
    public function setBc($bcEnabled)
    {
        $this->bcEnabled = $bcEnabled;
    }

    /**
     * @param string $template
     * @param array $parameters
     *
     * @return string
     */
    protected function renderBc($template, $parameters)
    {
        if ($this->bcEnabled) {
            // Sensio Generator 2.2
            return $this->render($this->skeletonDirs, $template, $parameters);
        }

        // Sensio Generator >=2.3
        return $this->render($template, $parameters);
    }

    /**
     * @param string $template
     * @param string $target
     * @param array  $parameters
     *
     * @return int
     */
    protected function renderFileBc($template, $target, $parameters)
    {
        if ($this->bcEnabled) {
            // Sensio Generator 2.2
            return $this->renderFile($this->skeletonDirs, $template, $target, $parameters);
        }

        // Sensio Generator >=2.3
        return $this->renderFile($template, $target, $parameters);
    }
}
