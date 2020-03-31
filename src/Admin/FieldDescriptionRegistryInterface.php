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

namespace Sonata\AdminBundle\Admin;

/**
 * Implementations should provide arrays of FieldDescriptionInterface instances.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface FieldDescriptionRegistryInterface
{
    /**
     * Return FormFieldDescription.
     */
    public function getFormFieldDescription(string $name): ?FieldDescriptionInterface;

    /**
     * Build and return the collection of form FieldDescription.
     *
     * @return FieldDescriptionInterface[] collection of form FieldDescription
     */
    public function getFormFieldDescriptions(): array;

    /**
     * Returns true if the admin has a FieldDescription with the given $name.
     */
    public function hasShowFieldDescription(string $name): bool;

    /**
     * Adds a FieldDescription.
     */
    public function addShowFieldDescription(string $name, FieldDescriptionInterface $fieldDescription): void;

    /**
     * Removes a ShowFieldDescription.
     */
    public function removeShowFieldDescription(string $name): void;

    /**
     * Adds a list FieldDescription.
     */
    public function addListFieldDescription(string $name, FieldDescriptionInterface $fieldDescription): void;

    /**
     * Removes a list FieldDescription.
     */
    public function removeListFieldDescription(string $name): void;

    /**
     * Returns a list depend on the given $object.
     */
    public function getList(): FieldDescriptionCollection;

    /**
     * Returns true if the filter FieldDescription exists.
     */
    public function hasFilterFieldDescription(string $name): bool;

    /**
     * Adds a filter FieldDescription.
     */
    public function addFilterFieldDescription(string $name, FieldDescriptionInterface $fieldDescription): void;

    /**
     * Removes a filter FieldDescription.
     */
    public function removeFilterFieldDescription(string $name): void;

    /**
     * Returns the filter FieldDescription collection.
     *
     * @return FieldDescriptionInterface[]
     */
    public function getFilterFieldDescriptions(): array;

    /**
     * Returns a filter FieldDescription.
     */
    public function getFilterFieldDescription(string $name): ?FieldDescriptionInterface;
}
