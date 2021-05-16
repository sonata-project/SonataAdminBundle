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

namespace Sonata\AdminBundle\FieldDescription;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface FieldDescriptionRegistryInterface
{
    public function getFormFieldDescription(string $name): FieldDescriptionInterface;

    public function hasFormFieldDescription(string $name): bool;

    public function addFormFieldDescription(string $name, FieldDescriptionInterface $fieldDescription): void;

    public function removeFormFieldDescription(string $name): void;

    /**
     * @return FieldDescriptionInterface[]
     */
    public function getFormFieldDescriptions(): array;

    public function getShowFieldDescription(string $name): FieldDescriptionInterface;

    public function hasShowFieldDescription(string $name): bool;

    public function addShowFieldDescription(string $name, FieldDescriptionInterface $fieldDescription): void;

    public function removeShowFieldDescription(string $name): void;

    /**
     * @return FieldDescriptionInterface[]
     */
    public function getShowFieldDescriptions(): array;

    public function hasListFieldDescription(string $name): bool;

    public function getListFieldDescription(string $name): FieldDescriptionInterface;

    public function addListFieldDescription(string $name, FieldDescriptionInterface $fieldDescription): void;

    public function removeListFieldDescription(string $name): void;

    /**
     * @return FieldDescriptionInterface[]
     */
    public function getListFieldDescriptions(): array;

    public function getFilterFieldDescription(string $name): FieldDescriptionInterface;

    public function hasFilterFieldDescription(string $name): bool;

    public function addFilterFieldDescription(string $name, FieldDescriptionInterface $fieldDescription): void;

    public function removeFilterFieldDescription(string $name): void;

    /**
     * @return FieldDescriptionInterface[]
     */
    public function getFilterFieldDescriptions(): array;
}
