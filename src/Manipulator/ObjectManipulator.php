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
            $object = self::callGetter($object, $parentAssociationMapping['fieldName']);
        }

        return self::callAdder($object, $instance, $associationMapping['fieldName']);
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
        if (null === $mappedBy) {
            return $instance;
        }

        $parentAssociationMappings = $parentFieldDescription->getParentAssociationMappings();

        foreach ($parentAssociationMappings as $parentAssociationMapping) {
            $object = self::callGetter($object, $parentAssociationMapping['fieldName']);
        }

        return self::callSetter($instance, $object, $mappedBy);
    }

    /**
     * Call $object->getXXX().
     */
    private static function callGetter(object $object, string $fieldName): object
    {
        $inflector = InflectorFactory::create()->build();
        $method = sprintf('get%s', $inflector->classify($fieldName));

        if (\is_callable([$object, $method]) && method_exists($object, $method)) {
            return $object->$method();
        }

        if (property_exists($object, $fieldName)) {
            $ref = new \ReflectionProperty($object, $fieldName);

            if ($ref->isPublic()) {
                return $object->$fieldName;
            }
        }

        throw new \BadMethodCallException(
            sprintf('Method %s::%s() does not exist.', ClassUtils::getClass($object), $method)
        );
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
        $inflector = InflectorFactory::create()->build();
        $method = sprintf('set%s', $inflector->classify($mappedBy));

        if (\is_callable([$instance, $method]) && method_exists($instance, $method)) {
            $instance->$method($object);

            return $instance;
        }

        if (property_exists($instance, $mappedBy)) {
            $ref = new \ReflectionProperty($instance, $mappedBy);

            if ($ref->isPublic()) {
                $instance->$mappedBy = $object;

                return $instance;
            }
        }

        throw new \BadMethodCallException(
            sprintf('Method %s::%s() does not exist.', ClassUtils::getClass($instance), $method)
        );
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
        $inflector = InflectorFactory::create()->build();
        $method = sprintf('add%s', $inflector->classify($fieldName));

        if (!(\is_callable([$object, $method]) && method_exists($object, $method))) {
            $method = rtrim($method, 's');

            if (!(\is_callable([$object, $method]) && method_exists($object, $method))) {
                $method = sprintf('add%s', $inflector->classify($inflector->singularize($fieldName)));

                if (!(\is_callable([$object, $method]) && method_exists($object, $method))) {
                    throw new \BadMethodCallException(
                        sprintf('Method %s::%s() does not exist.', ClassUtils::getClass($object), $method)
                    );
                }
            }
        }

        $object->$method($instance);

        return $instance;
    }
}
