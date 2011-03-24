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
     * apply the filter to the QueryBuilder instance
     *
     * @abstract
     * @param  $queryBuilder
     * @param string $alias
     * @param string $field
     * @param string $value
     * @return void
     */
    function filter($queryBuilder, $alias, $field, $value);

    /**
     * get the related form field filter
     *
     * @abstract
     * @return Field
     */
    function getFormField();
}
