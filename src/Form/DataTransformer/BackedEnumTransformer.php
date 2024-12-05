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

namespace Sonata\AdminBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

/**
 * @phpstan-template T of \BackedEnum
 * @phpstan-implements DataTransformerInterface<T, int|string>
 */
final class BackedEnumTransformer implements DataTransformerInterface
{
    /**
     * @phpstan-param class-string<T> $className
     */
    public function __construct(
        private string $className,
    ) {
    }

    /**
     * @param int|string|null $value
     *
     * @phpstan-return T|null
     */
    public function reverseTransform($value): ?\BackedEnum
    {
        if (null === $value || '' === $value) {
            return null;
        }

        if (!\is_int($value) && !\is_string($value)) {
            throw new TransformationFailedException(\sprintf('Could not transform value: expecting an int or string, got "%s".', get_debug_type($value)));
        }

        try {
            return $this->className::from($value);
        } catch (\ValueError|\TypeError) {
            throw new TransformationFailedException(\sprintf('Could not transform value "%s".', $value));
        }
    }

    /**
     * @param \BackedEnum|null $value
     *
     * @phpstan-param T|null $value
     */
    public function transform($value): string|int|null
    {
        if (null === $value) {
            return null;
        }

        if (!$value instanceof \BackedEnum) {
            throw new UnexpectedTypeException($value, \BackedEnum::class);
        }

        return $value->value;
    }
}
