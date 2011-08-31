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

interface FilterInterface
{
    /**
     * Apply the filter to the QueryBuilder instance
     *
     * @abstract
     * @param $queryBuilder
     * @param string $alias
     * @param string $field
     * @param string $value
     * @return void
     */
    function filter($queryBuilder, $alias, $field, $value);

    /**
     * Returns the filter name
     * @abstract
     * @return string
     */
    function getName();

    /**
     * Returns the label name
     *
     * @abstract
     * @return void
     */
    function getLabel();

    /**
     * @abstract
     * @return array
     */
    function getDefaultOptions();

    /**
     * @abstract
     * @param string $name
     * @param null $default
     * @return void
     */
    function getOption($name, $default = null);

    /**
     * @abstract
     * @param $name
     * @param array $options
     * @return void
     */
    function initialize($name, array $options = array());

        /**
     * @abstract
     * @return void
     */
    function getFieldName();

    /**
     * @abstract
     * @return void
     */
    function getFieldOptions();

    /**
     * @abstract
     * @return void
     */
    function getFieldType();

    /**
     * Returns the main widget used to render the filter
     *
     * @abstract
     * @return void
     */
    function getRenderSettings();
}
