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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sonata\AdminBundle\BCLayer\BCHelper;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Model\ProxyResolverInterface;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Transform object to ID and property label.
 *
 * @author Andrej Hudec <pulzarraider@gmail.com>
 *
 * @phpstan-template T of object
 * @phpstan-template P
 * @phpstan-implements DataTransformerInterface<T|array<T>|\Traversable<T>, int|string|array<int|string|array<string>>>
 */
final class ModelToIdPropertyTransformer implements DataTransformerInterface
{
    /**
     * @phpstan-var ModelManagerInterface<T>
     */
    private ModelManagerInterface $modelManager;

    /**
     * @phpstan-var class-string<T>
     */
    private string $className;

    /**
     * @var string|string[]
     *
     * @phpstan-var P
     */
    private $property;

    private bool $multiple;

    /**
     * @var callable|null
     *
     * @phpstan-var null|callable(T, P): string
     */
    private $toStringCallback;

    /**
     * @param string|string[] $property
     *
     * @phpstan-param ModelManagerInterface<T> $modelManager
     * @phpstan-param class-string<T> $className
     * @phpstan-param P $property
     * @phpstan-param null|callable(T, P): string $toStringCallback
     */
    public function __construct(
        ModelManagerInterface $modelManager,
        string $className,
        $property,
        bool $multiple = false,
        ?callable $toStringCallback = null
    ) {
        $this->modelManager = $modelManager;
        $this->className = $className;
        $this->property = $property;
        $this->multiple = $multiple;
        $this->toStringCallback = $toStringCallback;
    }

    /**
     * @param int|string|array<int|string|array<string>>|null $value
     *
     * @throws \UnexpectedValueException
     *
     * @return Collection<int|string, object>|object|null
     *
     * @phpstan-param int|string|array<int|string|array<string>>|null $value
     * @psalm-param int|string|(array{_labels?: array<string>}&array<int|string>)|null $value
     * @phpstan-return Collection<array-key, T>|T|null
     */
    public function reverseTransform($value)
    {
        if (null === $value || [] === $value || '' === $value) {
            if ($this->multiple) {
                return new ArrayCollection();
            }

            return null;
        }

        if (!$this->multiple) {
            if (\is_array($value)) {
                throw new \UnexpectedValueException('Value should not be an array.');
            }

            return $this->modelManager->find($this->className, $value);
        }

        if (!\is_array($value)) {
            throw new \UnexpectedValueException(sprintf('Value should be array, %s given.', \gettype($value)));
        }

        unset($value['_labels']);

        return (new ModelsToArrayTransformer($this->modelManager, $this->className))->reverseTransform($value);
    }

    /**
     * @param object|array<object>|\Traversable<object>|null $value
     *
     * @throws \InvalidArgumentException
     *
     * @return array<string|int, int|string|array<string>>
     *
     * @phpstan-param T|array<T>|\Traversable<T>|null $value
     * @phpstan-return array<int|string|array<string>>
     * @psalm-return array{_labels?: array<string>}&array<int|string>
     */
    public function transform($value): array
    {
        $result = [];

        if (null === $value) {
            return $result;
        }

        if ($this->multiple) {
            if (!\is_array($value) && substr(\get_class($value), -1 * \strlen($this->className)) === $this->className) {
                throw new \InvalidArgumentException(
                    'A multiple selection must be passed a collection not a single value.'
                    .' Make sure that form option "multiple=false" is set for many-to-one relation and "multiple=true"'
                    .' is set for many-to-many or one-to-many relations.'
                );
            }
            if (is_iterable($value)) {
                $collection = $value;
            } else {
                throw new \InvalidArgumentException(
                    'A multiple selection must be passed a collection not a single value.'
                    .' Make sure that form option "multiple=false" is set for many-to-one relation and "multiple=true"'
                    .' is set for many-to-many or one-to-many relations.'
                );
            }
        } else {
            if (!\is_array($value) && substr(\get_class($value), -1 * \strlen($this->className)) === $this->className) {
                $collection = [$value];
            } elseif (is_iterable($value)) {
                throw new \InvalidArgumentException(
                    'A single selection must be passed a single value not a collection.'
                    .' Make sure that form option "multiple=false" is set for many-to-one relation and "multiple=true"'
                    .' is set for many-to-many or one-to-many relations.'
                );
            } else {
                $collection = [$value];
            }
        }

        if ('' === $this->property) {
            throw new \RuntimeException('Please define "property" parameter.');
        }

        $labels = [];

        /** @phpstan-var array<T>|\Traversable<T> $collection */
        foreach ($collection as $model) {
            $id = current($this->modelManager->getIdentifierValues($model));

            if (null !== $this->toStringCallback) {
                $label = ($this->toStringCallback)($model, $this->property);
            } elseif (method_exists($model, '__toString')) {
                $label = $model->__toString();
            } else {
                $class = $this->modelManager instanceof ProxyResolverInterface
                    ? $this->modelManager->getRealClass($model)
                    // NEXT_MAJOR: Change to `\get_class`
                    : BCHelper::getClass($model);

                throw new \RuntimeException(sprintf(
                    'Unable to convert the entity %s to String, entity must have a \'__toString()\' method defined',
                    $class
                ));
            }

            $result[] = $id;
            $labels[] = $label;
        }

        if ([] !== $labels) {
            $result['_labels'] = $labels;
        }

        return $result;
    }
}
