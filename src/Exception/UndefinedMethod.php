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
final class UndefinedMethod extends LogicException implements SonataException
{
    /**
     * @param string $method
     *
     * @return self
     */
    public static function create($method)
    {
        return new self('Call to undefined method '.$method);
    }
}
