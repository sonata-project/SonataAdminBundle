<?php

declare(strict_types=1);

/*
 * This file is part of sensiolabs-de/admin-bundle.
 *
 * (c) SensioLabs Deutschland <info@sensiolabs.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\AdminBundle\Util;

use SensioLabs\AdminBundle\Exception\AbstractClassException;

/**
 * @internal
 */
final class Instantiator
{
    /**
     * @template T of object
     *
     * @phpstan-param class-string<T> $class
     * @phpstan-return T
     */
    public static function instantiate(string $class): object
    {
        $r = new \ReflectionClass($class);
        if ($r->isAbstract()) {
            throw new AbstractClassException($class);
        }

        $constructor = $r->getConstructor();

        if (null !== $constructor && (!$constructor->isPublic() || $constructor->getNumberOfRequiredParameters() > 0)) {
            return $r->newInstanceWithoutConstructor();
        }

        return new $class();
    }
}
