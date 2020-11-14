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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @author Javier Spagnoletti <phansys@gmail.com>
 */
final class TraversableToCollection
{
    /**
     * @param mixed $value
     *
     * @throws \TypeError
     *
     * @return Collection<int|string, mixed>
     *
     * @phpstan-return Collection<array-key, mixed>
     */
    public static function transform($value): Collection
    {
        if ($value instanceof Collection) {
            return $value;
        }

        if ($value instanceof \Traversable) {
            return new ArrayCollection(iterator_to_array($value));
        }

        if (\is_array($value)) {
            return new ArrayCollection($value);
        }

        throw new \TypeError(sprintf(
            'Argument 1 passed to "%s()" must be of type "%s" or "%s", %s given.',
            __METHOD__,
            \Traversable::class,
            'array',
            \is_object($value) ? 'instance of "'.\get_class($value).'"' : '"'.\gettype($value).'"'
        ));
    }
}
