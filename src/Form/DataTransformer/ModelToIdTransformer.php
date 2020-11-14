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

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * @phpstan-template T of object
 */
final class ModelToIdTransformer implements DataTransformerInterface
{
    /**
     * @var ModelManagerInterface
     */
    private $modelManager;

    /**
     * @var string
     *
     * @phpstan-var class-string<T>
     */
    private $className;

    /**
     * @phpstan-param class-string<T> $className
     */
    public function __construct(ModelManagerInterface $modelManager, string $className)
    {
        $this->modelManager = $modelManager;
        $this->className = $className;
    }

    /**
     * @param mixed $value
     *
     * @phpstan-return T|null
     */
    public function reverseTransform($value): ?object
    {
        if (empty($value) && !\in_array($value, ['0', 0], true)) {
            return null;
        }

        return $this->modelManager->find($this->className, $value);
    }

    /**
     * @param object|null $value
     *
     * @phpstan-param T|null $value
     */
    public function transform($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        return $this->modelManager->getNormalizedIdentifier($value);
    }
}
