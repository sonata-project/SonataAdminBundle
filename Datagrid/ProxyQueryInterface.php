<?php
/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) Jonathan H. Wage <jonwage@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Datagrid;

/**
 * Interface used by the Datagrid to build the query
 */
interface ProxyQueryInterface
{
    /**
     *
     * @param array $params
     * @param null $hydrationMode
     * @return mixed
     */
    function execute(array $params = array(), $hydrationMode = null);

    /**
     * @param $name
     * @param $args
     * @return mixed
     */
    function __call($name, $args);

    /**
     * @param $parentAssociationMappings
     * @param $fieldMapping
     * @return mixed
     */
    function setSortBy($parentAssociationMappings, $fieldMapping);

    /**
     * @return mixed
     */
    function getSortBy();

    /**
     * @param $sortOrder
     * @return mixed
     */
    function setSortOrder($sortOrder);

    /**
     * @abstract
     * @return mixed
     */
    function getSortOrder();

    /**
     * @return mixed
     */
    function getSingleScalarResult();

    /**
     * @param $firstResult
     * @return mixed
     */
    function setFirstResult($firstResult);

    /**
     * @return mixed
     */
    function getFirstResult();

    /**
     * @param $maxResults
     * @return mixed
     */
    function setMaxResults($maxResults);

    /**
     * @return mixed
     */
    function getMaxResults();

    /**
     * @return mixed
     */
    function getUniqueParameterId();

    /**
     * @param array $associationMappings
     * @return mixed
     */
    function entityJoin(array $associationMappings);
}
