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
 *
 * @method string|null getTargetModel()
 * @method bool        hasAdmin()
 * @method bool        hasParent()
 * @method bool        hasAssociationAdmin()
 * @method void        setHelp(string $help)
 */
interface FieldDescriptionInterface
{
    /**
     * set the field name.
     */
    public function setFieldName(?string $fieldName): void;

    /**
     * Returns the field name.
     *
     * @return string the field name
     */
    public function getFieldName(): ?string;

    /**
     * Set the name.
     */
    public function setName(?string $name): void;

    /**
     * Returns the name, the name can be used as a form label or table header.
     *
     * @return string the name
     */
    public function getName(): ?string;

    /**
     * Returns the value represented by the provided name.
     *
     * @param mixed|null $default
     *
     * @return mixed the value represented by the provided name
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

    /**
     * Returns the template name.
     *
     * @return string|null the template name
     */
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
    public function setParent(AdminInterface $parent);

    /**
     * Returns the parent Admin (only used in nested admin).
     *
     * @return AdminInterface|null // NEXT_MAJOR: Return AdminInterface
     */
    public function getParent(): ?AdminInterface;

    // NEXT_MAJOR: Uncomment the following line
    // public function hasParent(): bool;

    /**
     * Define the association mapping definition.
     */
    public function setAssociationMapping(array $associationMapping): void;

    /**
     * Returns the association mapping definition.
     */
    public function getAssociationMapping(): array;

    /**
     * NEXT_MAJOR: Remove this method in favor of `getTargetModel()`.
     *
     * Returns the related Target object model.
     *
     * @deprecated since sonata-project/admin-bundle 3.69. Use `getTargetModel()` instead.
     */
    public function getTargetEntity(): ?string;

    // public function getTargetModel(): ?string;

    /**
     * Sets the field mapping information.
     */
    public function setFieldMapping(array $fieldMapping): void;

    /**
     * Returns the field mapping definition.
     *
     * @return array the field mapping definition
     */
    public function getFieldMapping(): array;

    /**
     * set the parent association mappings information.
     */
    public function setParentAssociationMappings(array $parentAssociationMappings): void;

    /**
     * Returns the parent association mapping definitions.
     *
     * @return array the parent association mapping definitions
     */
    public function getParentAssociationMappings(): array;

    /**
     * set the association admin instance (only used if the field is linked to an Admin).
     *
     * @param AdminInterface $associationAdmin the associated admin
     */
    public function setAssociationAdmin(AdminInterface $associationAdmin);

    /**
     * Returns the associated Admin instance (only used if the field is linked to an Admin).
     *
     * @return AdminInterface|null // NEXT_MAJOR: Return AdminInterface
     */
    public function getAssociationAdmin(): ?AdminInterface;

    // NEXT_MAJOR: Uncomment the following line
    // public function hasAssociationAdmin(): bool;

    /**
     * Returns true if the FieldDescription is linked to an identifier field.
     */
    public function isIdentifier(): bool;

    /**
     * Returns the value linked to the description.
     *
     * @return bool|mixed
     */
    public function getValue(object $object);

    /**
     * set the admin class linked to this FieldDescription.
     */
    public function setAdmin(AdminInterface $admin);

    /**
     * @return AdminInterface the admin class linked to this FieldDescription
     */
    public function getAdmin(): AdminInterface;

    // NEXT_MAJOR: Uncomment the following line
    // public function hasAdmin(): bool;

    /**
     * merge option values related to the provided option name.
     *
     * @throws \RuntimeException
     */
    public function mergeOption(string $name, array $options = []): void;

    /**
     * merge options values.
     */
    public function mergeOptions(array $options = []): void;

    /**
     * set the original mapping type (only used if the field is linked to an entity).
     *
     * @param string|int $mappingType
     */
    public function setMappingType($mappingType);

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
     * @return array the field mapping definition
     */
    public function getSortFieldMapping(): array;

    /**
     * Returns the parent association mapping definitions used when sorting.
     *
     * @return array the parent association mapping definitions
     */
    public function getSortParentAssociationMapping(): array;

    /**
     * @return mixed
     */
    public function getFieldValue(?object $object, ?string $fieldName);

//    NEXT_MAJOR: uncomment this method in 4.0
//    /**
//     * Defines the help message.
//     */
//    public function setHelp(string $help): void;
}
