<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Form\DataTransformer;

use Doctrine\Common\Util\ClassUtils;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Transform object to ID and property label.
 *
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class ModelToIdPropertyTransformer implements DataTransformerInterface
{
    /**
     * @var ModelManagerInterface
     */
    protected $modelManager;

    /**
     * @var string
     */
    protected $className;

    /**
     * @var string
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
     * @param string        $className
     * @param string        $property
     * @param bool          $multiple
     * @param callable|null $toStringCallback
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

    public function reverseTransform($value)
    {
        $collection = $this->modelManager->getModelCollectionInstance($this->className);

        if (empty($value)) {
            if ($this->multiple) {
                return $collection;
            }

            return;
        }

        if (!$this->multiple) {
            return $this->modelManager->find($this->className, $value);
        }

        if (!is_array($value)) {
            throw new \UnexpectedValueException(sprintf('Value should be array, %s given.', gettype($value)));
        }

        foreach ($value as $key => $id) {
            if ('_labels' === $key) {
                continue;
            }

            $collection->add($this->modelManager->find($this->className, $id));
        }

        return $collection;
    }

    public function transform($entityOrCollection)
    {
        $result = [];

        if (!$entityOrCollection) {
            return $result;
        }

        if ($this->multiple) {
            $isArray = is_array($entityOrCollection);
            if (!$isArray && substr(get_class($entityOrCollection), -1 * strlen($this->className)) == $this->className) {
                throw new \InvalidArgumentException('A multiple selection must be passed a collection not a single value. Make sure that form option "multiple=false" is set for many-to-one relation and "multiple=true" is set for many-to-many or one-to-many relations.');
            } elseif ($isArray || ($entityOrCollection instanceof \ArrayAccess)) {
                $collection = $entityOrCollection;
            } else {
                throw new \InvalidArgumentException('A multiple selection must be passed a collection not a single value. Make sure that form option "multiple=false" is set for many-to-one relation and "multiple=true" is set for many-to-many or one-to-many relations.');
            }
        } else {
            if (substr(get_class($entityOrCollection), -1 * strlen($this->className)) == $this->className) {
                $collection = [$entityOrCollection];
            } elseif ($entityOrCollection instanceof \ArrayAccess) {
                throw new \InvalidArgumentException('A single selection must be passed a single value not a collection. Make sure that form option "multiple=false" is set for many-to-one relation and "multiple=true" is set for many-to-many or one-to-many relations.');
            } else {
                $collection = [$entityOrCollection];
            }
        }

        if (empty($this->property)) {
            throw new \RuntimeException('Please define "property" parameter.');
        }

        foreach ($collection as $entity) {
            $id = current($this->modelManager->getIdentifierValues($entity));

            if (null !== $this->toStringCallback) {
                if (!is_callable($this->toStringCallback)) {
                    throw new \RuntimeException('Callback in "to_string_callback" option doesn`t contain callable function.');
                }

                $label = call_user_func($this->toStringCallback, $entity, $this->property);
            } else {
                try {
                    $label = (string) $entity;
                } catch (\Exception $e) {
                    throw new \RuntimeException(sprintf("Unable to convert the entity %s to String, entity must have a '__toString()' method defined", ClassUtils::getClass($entity)), 0, $e);
                }
            }

            $result[] = $id;
            $result['_labels'][] = $label;
        }

        return $result;
    }
}
