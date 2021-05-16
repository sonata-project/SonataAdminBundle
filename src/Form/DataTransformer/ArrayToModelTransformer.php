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
use Sonata\AdminBundle\Util\Instantiator;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * @phpstan-template T of object
 */
final class ArrayToModelTransformer implements DataTransformerInterface
{
    /**
     * @var ModelManagerInterface
     * @phpstan-var ModelManagerInterface<T>
     */
    private $modelManager;

    /**
     * @var string
     *
     * @phpstan-var class-string<T>
     */
    private $className;

    /**
     * @phpstan-param ModelManagerInterface<T> $modelManager
     * @phpstan-param class-string<T>          $className
     */
    public function __construct(ModelManagerInterface $modelManager, string $className)
    {
        $this->modelManager = $modelManager;
        $this->className = $className;
    }

    /**
     * @param object|array<string, mixed>|null $value
     *
     * @phpstan-param T|array<string, mixed>|null $value
     *
     * @phpstan-return T
     */
    public function reverseTransform($value): object
    {
        // when the object is created the form return an array
        // one the object is persisted, the edit $array is the user instance
        if ($value instanceof $this->className) {
            return $value;
        }

        $instance = Instantiator::instantiate($this->className);

        if (!\is_array($value)) {
            return $instance;
        }

        $this->modelManager->reverseTransform($instance, $value);

        return $instance;
    }

    /**
     * @param object|null $value
     *
     * @return object|null
     *
     * @phpstan-param T|null $value
     *
     * @phpstan-return T|null
     */
    public function transform($value)
    {
        return $value;
    }
}
