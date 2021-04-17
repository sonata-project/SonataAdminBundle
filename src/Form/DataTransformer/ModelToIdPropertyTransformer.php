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
use Doctrine\Common\Util\ClassUtils;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Util\TraversableToCollection;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Transform object to ID and property label.
 *
 * @final since sonata-project/admin-bundle 3.52
 *
 * @author Andrej Hudec <pulzarraider@gmail.com>
 *
 * @phpstan-template T of object
 */
class ModelToIdPropertyTransformer implements DataTransformerInterface
{
    /**
     * @var ModelManagerInterface
     * @phpstan-var ModelManagerInterface<T>
     */
    protected $modelManager;

    /**
     * @var string
     *
     * @phpstan-var class-string<T>
     */
    protected $className;

    /**
     * @var string|string[]
     */
    protected $property;

    /**
     * @var bool
     */
    protected $multiple;

    /**
     * @var callable|null
     */
    protected $toStringCallback;

    /**
     * @param string          $className
     * @param string|string[] $property
     * @param bool            $multiple
     * @param callable|null   $toStringCallback
     *
     * @phpstan-template P
     * @phpstan-param ModelManagerInterface<T>         $modelManager
     * @phpstan-param class-string<T>                  $className
     * @phpstan-param null|callable(object, P): string $toStringCallback
     */
    public function __construct(
        ModelManagerInterface $modelManager,
        $className,
        $property,
        $multiple = false,
        $toStringCallback = null
    ) {
        $this->modelManager = $modelManager;
        $this->className = $className;
        $this->property = $property;
        $this->multiple = $multiple;
        $this->toStringCallback = $toStringCallback;
    }

    /**
     * @param mixed $value
     *
     * @return Collection<int|string, object>|object|null
     *
     * @phpstan-return Collection<array-key, T>|T|null
     */
    public function reverseTransform($value)
    {
        if (empty($value)) {
            if ($this->multiple) {
                return new ArrayCollection();
            }

            return null;
        }

        if (!$this->multiple) {
            return $this->modelManager->find($this->className, $value);
        }

        if (!\is_array($value)) {
            throw new \UnexpectedValueException(sprintf('Value should be array, %s given.', \gettype($value)));
        }

        foreach ($value as $key => $id) {
            if ('_labels' === $key) {
                unset($value[$key]);

                continue;
            }

            $value[$key] = (string) $id;
        }

        if ([] === $value) {
            $result = $value;
        } else {
            $query = $this->modelManager->createQuery($this->className);
            $this->modelManager->addIdentifiersToQuery($this->className, $query, $value);
            $result = $this->modelManager->executeQuery($query);
        }

        return TraversableToCollection::transform($result);
    }

    /**
     * @param object|object[]|null $value
     *
     * @return mixed[]
     *
     * @phpstan-param T|T[]|null $value
     */
    public function transform($value)
    {
        $result = [];

        if (!$value) {
            return $result;
        }

        if ($this->multiple) {
            $isArray = \is_array($value);
            if (!$isArray && substr(\get_class($value), -1 * \strlen($this->className)) === $this->className) {
                throw new \InvalidArgumentException(
                    'A multiple selection must be passed a collection not a single value.'
                    .' Make sure that form option "multiple=false" is set for many-to-one relation and "multiple=true"'
                    .' is set for many-to-many or one-to-many relations.'
                );
            }
            if ($isArray || ($value instanceof \ArrayAccess)) {
                $collection = $value;
            } else {
                throw new \InvalidArgumentException(
                    'A multiple selection must be passed a collection not a single value.'
                    .' Make sure that form option "multiple=false" is set for many-to-one relation and "multiple=true"'
                    .' is set for many-to-many or one-to-many relations.'
                );
            }
        } else {
            if (substr(\get_class($value), -1 * \strlen($this->className)) === $this->className) {
                $collection = [$value];
            } elseif ($value instanceof \ArrayAccess) {
                throw new \InvalidArgumentException(
                    'A single selection must be passed a single value not a collection.'
                    .' Make sure that form option "multiple=false" is set for many-to-one relation and "multiple=true"'
                    .' is set for many-to-many or one-to-many relations.'
                );
            } else {
                $collection = [$value];
            }
        }

        if (empty($this->property)) {
            throw new \RuntimeException('Please define "property" parameter.');
        }

        foreach ($collection as $model) {
            $id = current($this->modelManager->getIdentifierValues($model));

            if (null !== $this->toStringCallback) {
                if (!\is_callable($this->toStringCallback)) {
                    throw new \RuntimeException(
                        'Callback in "to_string_callback" option doesn`t contain callable function.'
                    );
                }

                $label = ($this->toStringCallback)($model, $this->property);
            } else {
                try {
                    $label = (string) $model;
                } catch (\Exception $e) {
                    throw new \RuntimeException(sprintf(
                        'Unable to convert the entity %s to String, entity must have a \'__toString()\' method defined',
                        ClassUtils::getClass($model)
                    ), 0, $e);
                }
            }

            $result[] = $id;
            $result['_labels'][] = $label;
        }

        return $result;
    }
}
