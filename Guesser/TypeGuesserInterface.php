<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Guesser;

use Sonata\AdminBundle\Model\ModelManagerInterface;

/**
 * Interface TypeGuesserInterface.
 *
 * @author  Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface TypeGuesserInterface
{
    /**
     * @param string                $class
     * @param string                $property
     * @param ModelManagerInterface $modelManager
     *
     * @return mixed
     */
    public function guessType($class, $property, ModelManagerInterface $modelManager);
}
