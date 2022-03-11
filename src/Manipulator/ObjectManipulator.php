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

namespace Sonata\AdminBundle\Manipulator;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\Inflector\InflectorFactory;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

final class ObjectManipulator
{
    /**
     * Add the instance to the object provided.
     *
     * @phpstan-template T of object
     * @phpstan-param T $instance
     * @phpstan-return T
     */
    public static function addInstance(
        object $object,
        object $instance,
        FieldDescriptionInterface $parentFieldDescription
    ): object {
        $associationMapping = $parentFieldDescription->getAssociationMapping();
        $parentAssociationMappings = $parentFieldDescription->getParentAssociationMappings();

        foreach ($parentAssociationMappings as $parentAssociationMapping) {
            $fieldName = $parentAssociationMapping['fieldName'];
            \assert(\is_string($fieldName));
            $object = self::callGetter($object, $fieldName);
        }

        $fieldName = $associationMapping['fieldName'];
        \assert(\is_string($fieldName));

        return self::callAdder($object, $instance, $fieldName);
    }

    /**
     * Set the object to the instance provided.
     *
     * @phpstan-template T of object
     * @phpstan-param T $instance
     * @phpstan-return T
     */
    public static function setObject(
        object $instance,
        object $object,
        FieldDescriptionInterface $parentFieldDescription
    ): object {
        $mappedBy = $parentFieldDescription->getAssociationMapping()['mappedBy'] ?? null;
        if (!\is_string($mappedBy)) {
            return $instance;
        }

        $parentAssociationMappings = $parentFieldDescription->getParentAssociationMappings();

        foreach ($parentAssociationMappings as $parentAssociationMapping) {
            $fieldName = $parentAssociationMapping['fieldName'];
            \assert(\is_string($fieldName));
            $object = self::callGetter($object, $fieldName);
        }

        return self::callSetter($instance, $object, $mappedBy);
    }

    /**
     * Call $object->getXXX().
     */
    private static function callGetter(object $object, string $fieldName): object
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        return $propertyAccessor->getValue($object, $fieldName);
    }

    /**
     * Call $instance->setXXX($object).
     *
     * @phpstan-template T of object
     * @phpstan-param T $instance
     * @phpstan-return T
     */
    private static function callSetter(object $instance, object $object, string $mappedBy): object
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        $propertyAccessor->setValue($instance, $mappedBy, $object);

        return $instance;
    }

    /**
     * Call $object->addXXX($instance).
     *
     * @phpstan-template T of object
     * @phpstan-param T $instance
     * @phpstan-return T
     */
    private static function callAdder(object $object, object $instance, string $fieldName): object
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        $collection = $propertyAccessor->getValue($object, $fieldName);
        $collection[] = $instance;
        $propertyAccessor->setValue($object, $fieldName, $collection);

        return $instance;
    }
}
