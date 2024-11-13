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

use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

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
        FieldDescriptionInterface $parentFieldDescription,
    ): object {
        $associationMapping = $parentFieldDescription->getAssociationMapping();
        $parentAssociationMappings = $parentFieldDescription->getParentAssociationMappings();

        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        foreach ($parentAssociationMappings as $parentAssociationMapping) {
            $fieldName = $parentAssociationMapping['fieldName'];
            \assert(\is_string($fieldName));

            $object = $propertyAccessor->getValue($object, $fieldName);
        }

        $fieldName = $associationMapping['fieldName'];
        \assert(\is_string($fieldName));

        $collection = $propertyAccessor->getValue($object, $fieldName);
        $collection[] = $instance;
        $propertyAccessor->setValue($object, $fieldName, $collection);

        return $instance;
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
        FieldDescriptionInterface $parentFieldDescription,
    ): object {
        $mappedBy = $parentFieldDescription->getAssociationMapping()['mappedBy'] ?? null;
        if (!\is_string($mappedBy)) {
            return $instance;
        }

        $parentAssociationMappings = $parentFieldDescription->getParentAssociationMappings();

        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        foreach ($parentAssociationMappings as $parentAssociationMapping) {
            $fieldName = $parentAssociationMapping['fieldName'];
            \assert(\is_string($fieldName));

            $object = $propertyAccessor->getValue($object, $fieldName);
        }

        $propertyAccessor->setValue($instance, $mappedBy, $object);

        /** @phpstan-var T */
        return $instance;
    }
}
