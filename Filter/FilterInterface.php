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
    public function filter(ProxyQueryInterface $queryBuilder, $alias, $field, $value);

    /**
     * @param mixed $query
     * @param mixed $value
     */
    public function apply($query, $value);

    /**
     * Returns the filter name
     *
     * @return string
     */
    public function getName();

    /**
     * Returns the filter form name
     *
     * @return string
     */
    public function getFormName();

    /**
     * Returns the label name
     *
     * @return string
     */
    public function getLabel();

    /**
     * @param string $label
     */
    public function setLabel($label);

    /**
     * @return array
     */
    public function getDefaultOptions();

    /**
     * @param string $name
     * @param null   $default
     *
     * @return mixed
     */
    public function getOption($name, $default = null);

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function setOption($name, $value);

    /**
     * @param string $name
     * @param array  $options
     *
     * @return void
     */
    public function initialize($name, array $options = array());

    /**
     * @return string
     */
    public function getFieldName();

    /**
     * @return array of mappings
     */
    public function getParentAssociationMappings();

    /**
     * @return array field mapping
     */
    public function getFieldMapping();

    /**
     * @return array association mapping
     */
    public function getAssociationMapping();

    /**
     * @return array
     */
    public function getFieldOptions();

    /**
     * @return string
     */
    public function getFieldType();

    /**
     * Returns the main widget used to render the filter
     *
     * @return array
     */
    public function getRenderSettings();
}
