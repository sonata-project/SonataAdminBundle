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

use RuntimeException;

/**
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
final class NoFieldDefined extends RuntimeException implements SonataException
{
    /**
     * @param string $method
     *
     * @return self
     */
    public static function forMethod($method)
    {
        return new self(sprintf(
            'No editable field defined. Did you forget to implement the "%s" method?',
            $method
        ));
    }
}
