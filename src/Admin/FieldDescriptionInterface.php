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
 */
interface FieldDescriptionInterface
{
    /**
     * set the field name.
     *
     * @param string $fieldName
     */
    public function setFieldName($fieldName);

    /**
     * Returns the field name.
     *
     * @return string the field name
     */
    public function getFieldName();

    /**
     * Set the name.
     *
     * @param string $name
     */
    public function setName($name);

    /**
     * Returns the name, the name can be used as a form label or table header.
     *
     * @return string the name
     */
    public function getName();

    /**
     * Returns the value represented by the provided name.
     *
     * @param string     $name
     * @param mixed|null $default
     *
     * @return mixed the value represented by the provided name
     */
    public function getOption($name, $default = null);

    /**
     * Define an option, an option is has a name and a value.
     *
     * @param string $name
     * @param mixed  $value
     */
    public function setOption($name, $value);

    /**
     * Define the options value, if the options array contains the reserved keywords
     *   - type
     *   - template.
     *
     * Then the value are copied across to the related property value
     */
    public function setOptions(array $options);

    /**
     * Returns options.
     *
     * @return array options
     */
    public function getOptions();

    /**
     * Returns the template used to render the field.
     *
     * @param string $template
     */
    public function setTemplate($template);

    /**
     * Returns the template name.
     *
     * @return string|null the template name
     */
    public function getTemplate();

    /**
     * Returns the field type, the type is a mandatory field as it used to select the correct template
     * or the logic associated to the current FieldDescription object.
     *
     * @param string $type
     */
    public function setType($type);

    /**
     * Returns the type.
     *
     * @return int|string
     */
    public function getType();

    /**
     * set the parent Admin (only used in nested admin).
     */
    public function setParent(AdminInterface $parent);

    /**
     * Returns the parent Admin (only used in nested admin).
     *
     * @return AdminInterface|null // NEXT_MAJOR: Return AdminInterface
     */
    public function getParent();

    // NEXT_MAJOR: Uncomment the following line
    // public function hasParent(): bool;

    /**
     * Define the association mapping definition.
     *
     * @param array $associationMapping
     */
    public function setAssociationMapping($associationMapping);

    /**
     * Returns the association mapping definition.
     *
     * @return array
     */
    public function getAssociationMapping();

    /**
     * NEXT_MAJOR: Remove this method in favor of `getTargetModel()`.
     *
     * Returns the related Target object model.
     *
     * @deprecated since sonata-project/admin-bundle 3.69. Use `getTargetModel()` instead.
     *
     * @return string|null
     */
    public function getTargetEntity();

    // public function getTargetModel(): ?string;

    /**
     * set the field mapping information.
     *
     * @param array $fieldMapping
     */
    public function setFieldMapping($fieldMapping);

    /**
     * Returns the field mapping definition.
     *
     * @return array the field mapping definition
     */
    public function getFieldMapping();

    /**
     * set the parent association mappings information.
     */
    public function setParentAssociationMappings(array $parentAssociationMappings);

    /**
     * Returns the parent association mapping definitions.
     *
     * @return array the parent association mapping definitions
     */
    public function getParentAssociationMappings();

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
    public function getAssociationAdmin();

    // NEXT_MAJOR: Uncomment the following line
    // public function hasAssociationAdmin(): bool;

    /**
     * Returns true if the FieldDescription is linked to an identifier field.
     *
     * @return bool
     */
    public function isIdentifier();

    /**
     * Returns the value linked to the description.
     *
     * @param object $object
     *
     * @return bool|mixed
     */
    public function getValue($object);

    /**
     * set the admin class linked to this FieldDescription.
     */
    public function setAdmin(AdminInterface $admin);

    /**
     * @return AdminInterface the admin class linked to this FieldDescription
     */
    public function getAdmin();

    // NEXT_MAJOR: Uncomment the following line
    // public function hasAdmin(): bool;

    /**
     * merge option values related to the provided option name.
     *
     * @param string $name
     *
     * @throws \RuntimeException
     */
    public function mergeOption($name, array $options = []);

    /**
     * merge options values.
     */
    public function mergeOptions(array $options = []);

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
     *
     * @return string
     */
    public function getTranslationDomain();

    /**
     * Returns true if field is sortable.
     *
     * @return bool
     */
    public function isSortable();

    /**
     * Returns the field mapping definition used when sorting.
     *
     * @return array the field mapping definition
     */
    public function getSortFieldMapping();

    /**
     * Returns the parent association mapping definitions used when sorting.
     *
     * @return array the parent association mapping definitions
     */
    public function getSortParentAssociationMapping();

    /**
     * @param object|null $object
     * @param string      $fieldName
     *
     * @return mixed
     */
    public function getFieldValue($object, $fieldName);
}
