<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Exception;

use LogicException;

/**
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
final class UnableToGenerateGroupAndLabel extends LogicException implements SonataException
{
    /**
     * @param string $class
     *
     * @return self
     */
    public static function forClass($class)
    {
        return new self(sprintf(
            'Unable to generate admin group and label for class %s.',
            $class
        ));
    }
}
