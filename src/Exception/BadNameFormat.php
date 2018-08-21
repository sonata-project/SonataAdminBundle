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

use InvalidArgumentException;

/**
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
final class BadNameFormat extends InvalidArgumentException implements SonataException
{
    const EXCEPTION_MESSAGE = 'The %s class name must not contain a ":" (colon sign) '
        .'("%s" given, expecting something like %s")';

    /**
     * @param string $admin
     *
     * @return self
     */
    public static function forAdmin($admin)
    {
        return new self(sprintf(
            self::EXCEPTION_MESSAGE,
            'admin',
            $admin,
            'PostAdmin'
        ));
    }

    /**
     * @param string $controller
     *
     * @return self
     */
    public static function forController($controller)
    {
        return new self(sprintf(
            self::EXCEPTION_MESSAGE,
            'controller',
            $controller,
            'PostAdminController'
        ));
    }
}
