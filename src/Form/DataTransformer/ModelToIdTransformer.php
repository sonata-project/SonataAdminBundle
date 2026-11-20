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

use Sonata\AdminBundle\Model\ModelManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * @phpstan-template T of object
 * @phpstan-implements DataTransformerInterface<T, int|string>
 */
final class ModelToIdTransformer implements DataTransformerInterface
{
    /**
     * @phpstan-param ModelManagerInterface<T> $modelManager
     * @phpstan-param class-string<T>          $className
     */
    public function __construct(
        private ModelManagerInterface $modelManager,
        private string $className,
        private bool $strict = false,
    ) {
    }

    /**
     * @param int|string|null $value
     *
     * @throws TransformationFailedException When strict and no model exists for the identifier
     *
     * @phpstan-return T|null
     */
    public function reverseTransform($value): ?object
    {
        if (null === $value || '' === $value) {
            return null;
        }

        $model = $this->modelManager->find($this->className, $value);

        // Without strict, an unknown identifier silently clears the association.
        if (null === $model && $this->strict) {
            throw new TransformationFailedException(\sprintf(
                'Unable to find a model of class "%s" with the identifier "%s".',
                $this->className,
                $value
            ));
        }

        return $model;
    }

    /**
     * @param object|null $value
     *
     * @phpstan-param T|null $value
     */
    public function transform($value): ?string
    {
        if (null === $value) {
            return null;
        }

        return $this->modelManager->getNormalizedIdentifier($value);
    }
}
