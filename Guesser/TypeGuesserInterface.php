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

namespace Sonata\AdminBundle\Guesser;

use Sonata\AdminBundle\Model\ModelManagerInterface;

interface TypeGuesserInterface
{
    /**
     * @param string                                          $class
     * @param string                                          $property
     * @param \Sonata\AdminBundle\Model\ModelManagerInterface $modelManager
     *
     * @return mixed
     */
    public function guessType($class, $property, ModelManagerInterface $modelManager);
}
