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

namespace Sonata\AdminBundle\Util;

/**
 * @internal
 */
final class Instantiator
{
    /**
     * @template T of object
     * @phpstan-param class-string<T> $class
     * @phpstan-return T
     */
    public static function instantiate(string $class): object
    {
        $r = new \ReflectionClass($class);
        if ($r->isAbstract()) {
            throw new \RuntimeException(sprintf('Cannot initialize abstract class: %s', $class));
        }

        $constructor = $r->getConstructor();

        if (null !== $constructor && (!$constructor->isPublic() || $constructor->getNumberOfRequiredParameters() > 0)) {
            return $r->newInstanceWithoutConstructor();
        }

        return new $class();
    }
}
