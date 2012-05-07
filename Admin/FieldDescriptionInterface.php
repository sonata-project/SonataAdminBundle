<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) 2010-2011 Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Admin;

use Sonata\AdminBundle\Admin\AdminInterface;

interface FieldDescriptionInterface
{

    /**
     * set the field name
     *
     * @param string $fieldName
     *
     * @return void
     */
    function setFieldName($fieldName);

    /**
     * return the field name
     *
     * @return string the field name
     */
    function getFieldName();

    /**
     * Set the name
     *
     * @param string $name
     *
     * @return void
     */
    function setName($name);

    /**
     * Return the name, the name can be used as a form label or table header
     *
     * @return string the name
     */
    function getName();

    /**
     * Return the value represented by the provided name
     *
     * @param string $name
     * @param null   $default
     *
     * @return array|null the value represented by the provided name
     */
    function getOption($name, $default = null);

    /**
     * Define an option, an option is has a name and a value
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return void set the option value
     */
    function setOption($name, $value);

    /**
     * Define the options value, if the options array contains the reserved keywords
     *   - type
     *   - template
     *
     * Then the value are copied across to the related property value
     *
     * @param array $options
     *
     * @return void
     */
    function setOptions(array $options);

    /**
     * return options
     *
     * @return array options
     */
    function getOptions();

    /**
     * return the template used to render the field
     *
     * @param string $template
     *
     * @return void
     */
    function setTemplate($template);

    /**
     * return the template name
     *
     * @return string the template name
     */
    function getTemplate();

    /**
     * return the field type, the type is a mandatory field as it used to select the correct template
     * or the logic associated to the current FieldDescription object
     *
     * @param string $type
     *
     * @return void the field type
     */
    function setType($type);

    /**
     * return the type
     *
     * @return int|string
     */
    function getType();

    /**
     * set the parent Admin (only used in nested admin)
     *
     * @param \Sonata\AdminBundle\Admin\AdminInterface $parent
     *
     * @return void
     */
    function setParent(AdminInterface $parent);

    /**
     * return the parent Admin (only used in nested admin)
     *
     * @return \Sonata\AdminBundle\Admin\AdminInterface
     */
    function getParent();

    /**
     * Define the association mapping definition
     *
     * @param array $associationMapping
     *
     * @return void
     */
    function setAssociationMapping($associationMapping);

    /**
     * return the association mapping definition
     *
     * @return array
     */
    function getAssociationMapping();

    /**
     * return the related Target Entity
     *
     * @return string|null
     */
    function getTargetEntity();

    /**
     * set the field mapping information
     *
     * @param array $fieldMapping
     *
     * @return void
     */
    function setFieldMapping($fieldMapping);

    /**
     * return the field mapping definition
     *
     * @return array the field mapping definition
     */
    function getFieldMapping();

    /**
     * set the parent association mappings information
     *
     * @param array $parentAssociationMappings
     *
     * @return void
     */
    function setParentAssociationMappings(array $parentAssociationMappings);

    /**
     * return the parent association mapping definitions
     *
     * @return array the parent association mapping definitions
     */
    function getParentAssociationMappings();

    /**
     * set the association admin instance (only used if the field is linked to an Admin)
     *
     * @param \Sonata\AdminBundle\Admin\AdminInterface $associationAdmin the associated admin
     */
    function setAssociationAdmin(AdminInterface $associationAdmin);

    /**
     * return the associated Admin instance (only used if the field is linked to an Admin)
     * @return \Sonata\AdminBundle\Admin\AdminInterface
     */
    function getAssociationAdmin();

    /**
     * return true if the FieldDescription is linked to an identifier field
     *
     * @return bool
     */
    function isIdentifier();

    /**
     * return the value linked to the description
     *
     * @param mixed $object
     *
     * @return bool|mixed
     */
    function getValue($object);

    /**
     * set the admin class linked to this FieldDescription
     *
     * @param \Sonata\AdminBundle\Admin\AdminInterface $admin
     *
     * @return void
     */
    function setAdmin(AdminInterface $admin);

    /**
     * @return \Sonata\AdminBundle\Admin\AdminInterface the admin class linked to this FieldDescription
     */
    function getAdmin();

    /**
     * merge option values related to the provided option name
     *
     * @throws \RuntimeException
     *
     * @param string $name
     * @param array  $options
     *
     * @return void
     */
    function mergeOption($name, array $options = array());

    /**
     * merge options values
     *
     * @param array $options
     *
     * @return void
     */
    function mergeOptions(array $options = array());

    /**
     * set the original mapping type (only used if the field is linked to an entity)
     *
     * @param string|int $mappingType
     *
     * @return void
     */
    function setMappingType($mappingType);

    /**
     * return the mapping type
     *
     * @return int|string
     */
    function getMappingType();

    /**
     * return the label to use for the current field
     *
     * @return string
     */
    function getLabel();

    /*
     * @return boolean
     */
    function isSortable();

    /**
     * return the field mapping definition used when sorting
     *
     * @return array the field mapping definition
     */
    function getSortFieldMapping();

    /**
     * return the parent association mapping definitions used when sorting
     *
     * @return array the parent association mapping definitions
     */
    function getSortParentAssociationMapping();

    /**
     *
     * @param object $object
     * @param string $fieldName
     *
     * @return mixed
     */
    function getFieldValue($object, $fieldName);
}