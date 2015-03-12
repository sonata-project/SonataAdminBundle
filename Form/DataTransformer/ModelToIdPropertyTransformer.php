<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Sonata\AdminBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Doctrine\Common\Util\ClassUtils;
use RuntimeException;

/**
 * Transform object to ID and property label
 *
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class ModelToIdPropertyTransformer implements DataTransformerInterface
{
    protected $modelManager;

    protected $className;

    protected $property;

    protected $multiple;

    protected $toStringCallback;

    /**
     * @param ModelManagerInterface $modelManager
     * @param string                $className
     * @param string                $property
     */
    public function __construct(ModelManagerInterface $modelManager, $className, $property, $multiple=false, $toStringCallback=null)
    {
        $this->modelManager     = $modelManager;
        $this->className        = $className;
        $this->property         = $property;
        $this->multiple         = $multiple;
        $this->toStringCallback = $toStringCallback;
    }

    /**
     * {@inheritDoc}
     */
    public function reverseTransform($value)
    {
        $collection = $this->modelManager->getModelCollectionInstance($this->className);

        if (empty($value) || empty($value['identifiers'])) {
            if (!$this->multiple) {
                return null;
            } else {
                return $collection;
            }
        }

        if (!$this->multiple) {
             return $this->modelManager->find($this->className, current($value['identifiers']));
        }

        foreach ($value['identifiers'] as $id) {
            $collection->add($this->modelManager->find($this->className, $id));
        }

        return $collection;
    }

    /**
     * {@inheritDoc}
     */
    public function transform($entityOrCollection)
    {
        $result = array('identifiers' => array(), 'labels' => array());

        if (!$entityOrCollection) {
            return $result;
        }
        if ($this->multiple) {
            if (substr(get_class($entityOrCollection), -1 * strlen($this->className)) == $this->className) {
                throw new \InvalidArgumentException('A multiple selection must be passed a collection not a single value. Make sure that form option "multiple=false" is set for many-to-one relation and "multiple=true" is set for many-to-many or one-to-many relations.');
            } elseif ($entityOrCollection instanceof \ArrayAccess) {
                $collection = $entityOrCollection;
            } else {
                throw new \InvalidArgumentException('A multiple selection must be passed a collection not a single value. Make sure that form option "multiple=false" is set for many-to-one relation and "multiple=true" is set for many-to-many or one-to-many relations.');
            }
        } else {
            if (substr(get_class($entityOrCollection), -1 * strlen($this->className)) == $this->className) {
                $collection = array($entityOrCollection);
            } elseif ($entityOrCollection instanceof \ArrayAccess) {
                throw new \InvalidArgumentException('A single selection must be passed a single value not a collection. Make sure that form option "multiple=false" is set for many-to-one relation and "multiple=true" is set for many-to-many or one-to-many relations.');
            } else {
                $collection = array($entityOrCollection);
            }
        }

        if (empty($this->property)) {
            throw new RuntimeException('Please define "property" parameter.');
        }

        foreach ($collection as $entity) {
            $id  = current($this->modelManager->getIdentifierValues($entity));

            if ($this->toStringCallback !== null) {
                if (!is_callable($this->toStringCallback)) {
                    throw new RuntimeException('Callback in "to_string_callback" option doesn`t contain callable function.');
                }

                $label = call_user_func($this->toStringCallback, $entity, $this->property);
            } else {
                try {
                    $label = (string) $entity;
                } catch (\Exception $e) {
                    throw new RuntimeException(sprintf("Unable to convert the entity %s to String, entity must have a '__toString()' method defined", ClassUtils::getClass($entity)), 0, $e);
                }
            }

            $result['identifiers'][] = $id;
            $result['labels'][] = $label;
        }

        return $result;
    }
}
