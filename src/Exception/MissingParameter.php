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
final class MissingParameter extends RuntimeException implements SonataException
{
    /**
     * @param string $controller
     * @param string $route
     *
     * @return self
     */
    public static function forControllerAndRoute($controller, $route)
    {
        return new self(sprintf(
            'There is no `_sonata_admin` defined for the controller `%s` and the current route `%s`',
            $controller,
            $route
        ));
    }
}
