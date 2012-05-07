<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Filter;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;

interface FilterInterface
{
    /**
     * Apply the filter to the QueryBuilder instance
     *
     * @param ProxyQueryInterface $queryBuilder
     * @param string              $alias
     * @param string              $field
     * @param string              $value
     *
     * @return void
     */
    function filter(ProxyQueryInterface $queryBuilder, $alias, $field, $value);

    /**
     * @param mixed $query
     * @param mixed $value
     */
    function apply($query, $value);

    /**
     * Returns the filter name
     *
     * @return string
     */
    function getName();

    /**
     * Returns the filter form name
     *
     * @return string
     */
    function getFormName();

    /**
     * Returns the label name
     *
     * @return string
     */
    function getLabel();

    /**
     * @param string $label
     */
    function setLabel($label);

    /**
     * @return array
     */
    function getDefaultOptions();

    /**
     * @param string $name
     * @param null   $default
     *
     * @return mixed
     */
    function getOption($name, $default = null);

    /**
     * @param string $name
     * @param mixed $value
     */
    function setOption($name, $value);

    /**
     * @param string $name
     * @param array  $options
     *
     * @return void
     */
    function initialize($name, array $options = array());

    /**
     * @return string
     */
    function getFieldName();

    /**
     * @return array of mappings
     */
    function getParentAssociationMappings();

    /**
     * @return array field mapping
     */
    function getFieldMapping();

    /**
     * @return array association mapping
     */
    function getAssociationMapping();

    /**
     * @return array
     */
    function getFieldOptions();

    /**
     * @return string
     */
    function getFieldType();

    /**
     * Returns the main widget used to render the filter
     *
     * @return array
     */
    function getRenderSettings();
}
