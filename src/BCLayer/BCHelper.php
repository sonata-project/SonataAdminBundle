<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\BCLayer;

use Doctrine\Common\Util\ClassUtils;
use Sonata\AdminBundle\Model\ProxyResolverInterface;

/**
 * @internal
 */
class BCHelper
{
    /**
     * @return class-string
     */
    public static function getClass(object $object): string
    {
        $classFromDoctrine = ClassUtils::getClass($object);
        $class = $object::class;

        if ($class !== $classFromDoctrine) {
            @trigger_error(\sprintf(
                'Using proxy class "%s" without a model manager which implements %s is deprecated'
                .' since sonata-project/admin-bundle version 4.17 and will not work in 5.0 version.',
                $class,
                ProxyResolverInterface::class
            ), \E_USER_DEPRECATED);
        }

        return $classFromDoctrine;
    }
}
