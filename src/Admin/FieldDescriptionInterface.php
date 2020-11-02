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
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface FieldDescriptionInterface
{
    public function setFieldName(?string $fieldName): void;

    /**
     * Returns the field name.
     *
     * @return string|null the field name
     */
    public function getFieldName(): ?string;

    public function setName(string $name): void;

    /**
     * Returns the name, the name can be used as a form label or table header.
     */
    public function getName(): string;

    /**
     * Returns the value represented by the provided name.
     *
     * @param mixed $default
     *
     * @return mixed
     */
    public function getOption(string $name, $default = null);

    /**
     * Define an option, an option is has a name and a value.
     *
     * @param mixed $value
     */
    public function setOption(string $name, $value): void;

    /**
     * Define the options value, if the options array contains the reserved keywords
     *   - type
     *   - template.
     *
     * Then the value are copied across to the related property value.
     *
     * @param array<string, mixed> $options
     */
    public function setOptions(array $options): void;

    /**
     * Returns options.
     *
     * @return array<string, mixed>
     */
    public function getOptions(): array;

    /**
     * Sets the template used to render the field.
     */
    public function setTemplate(?string $template): void;

    public function getTemplate(): ?string;

    /**
     * Sets the field type. The type is a mandatory field as it's used to select the correct template
     * or the logic associated to the current FieldDescription object.
     */
    public function setType(?string $type): void;

    /**
     * Returns the type.
     */
    public function getType(): ?string;

    /**
     * set the parent Admin (only used in nested admin).
     */
    public function setParent(AdminInterface $parent): void;

    /**
     * Returns the parent Admin (only used in nested admin).
     *
     * @throws \LogicException
     */
    public function getParent(): AdminInterface;

    public function hasParent(): bool;

    /**
     * Define the association mapping definition.
     *
     * @param array<string, mixed> $associationMapping
     */
    public function setAssociationMapping(array $associationMapping): void;

    /**
     * Returns the association mapping definition.
     *
     * @return array<string, mixed>
     */
    public function getAssociationMapping(): array;

    /**
     * Returns the related Target object model.
     */
    public function getTargetModel(): ?string;

    /**
     * Sets the field mapping information.
     *
     * @param array<string, mixed> $fieldMapping
     */
    public function setFieldMapping(array $fieldMapping): void;

    /**
     * Returns the field mapping definition.
     *
     * @return array<string, mixed>
     */
    public function getFieldMapping(): array;

    /**
     * set the parent association mappings information.
     *
     *  @param array<array<string, mixed>> $parentAssociationMappings
     */
    public function setParentAssociationMappings(array $parentAssociationMappings): void;

    /**
     * Returns the parent association mapping definitions.
     *
     * @return array<array<string, mixed>>
     */
    public function getParentAssociationMappings(): array;

    /**
     * Set the association admin instance (only used if the field is linked to an Admin).
     */
    public function setAssociationAdmin(AdminInterface $associationAdmin): void;

    /**
     * Returns the associated Admin instance (only used if the field is linked to an Admin).
     *
     * @throws \LogicException
     */
    public function getAssociationAdmin(): AdminInterface;

    public function hasAssociationAdmin(): bool;

    /**
     * Returns true if the FieldDescription is linked to an identifier field.
     */
    public function isIdentifier(): bool;

    /**
     * Returns the value linked to the description.
     *
     * @return mixed
     */
    public function getValue(object $object);

    public function setAdmin(AdminInterface $admin): void;

    /**
     * @throws \LogicException
     */
    public function getAdmin(): AdminInterface;

    public function hasAdmin(): bool;

    /**
     * Merge option values related to the provided option name.
     *
     * @param array<string, mixed> $options
     *
     * @throws \RuntimeException
     */
    public function mergeOption(string $name, array $options = []): void;

    /**
     * Merge options values.
     *
     * @param array<string, mixed> $options
     */
    public function mergeOptions(array $options = []): void;

    /**
     * set the original mapping type (only used if the field is linked to an entity).
     *
     * @param int|string $mappingType
     */
    public function setMappingType($mappingType): void;

    /**
     * Returns the mapping type.
     *
     * @return int|string
     */
    public function getMappingType();

    /**
     * Returns the label to use for the current field.
     * Use null to fallback to the default label and false to hide the label.
     *
     * @return string|false|null
     */
    public function getLabel();

    /**
     * Returns the translation domain to use for the current field.
     */
    public function getTranslationDomain(): string;

    /**
     * Returns true if field is sortable.
     */
    public function isSortable(): bool;

    /**
     * Returns the field mapping definition used when sorting.
     *
     * @return array<string, mixed>
     */
    public function getSortFieldMapping(): array;

    /**
     * Returns the parent association mapping definitions used when sorting.
     *
     * @return array<string, mixed>
     */
    public function getSortParentAssociationMapping(): array;

    /**
     * @return mixed
     */
    public function getFieldValue(?object $object, ?string $fieldName);
}
