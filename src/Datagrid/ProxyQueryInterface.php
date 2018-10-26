<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Datagrid;

/**
 * Used by the Datagrid to build the query.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface ProxyQueryInterface
{
    /**
     * @param string $name
     * @param array  $args
     *
     * @return mixed
     */
    public function __call($name, $args);

    /**
     * @param int|null $hydrationMode
     *
     * @return mixed
     */
    public function execute(array $params = [], $hydrationMode = null);

    /**
     * @param array $parentAssociationMappings
     * @param array $fieldMapping
     *
     * @return ProxyQueryInterface
     */
    public function setSortBy($parentAssociationMappings, $fieldMapping);

    /**
     * @return mixed
     */
    public function getSortBy();

    /**
     * @param mixed $sortOrder
     *
     * @return ProxyQueryInterface
     */
    public function setSortOrder($sortOrder);

    /**
     * @return mixed
     */
    public function getSortOrder();

    /**
     * @return mixed
     */
    public function getSingleScalarResult();

    /**
     * @param int|null $firstResult
     *
     * @return ProxyQueryInterface
     */
    public function setFirstResult($firstResult);

    /**
     * @return mixed
     */
    public function getFirstResult();

    /**
     * @param int|null $maxResults
     *
     * @return ProxyQueryInterface
     */
    public function setMaxResults($maxResults);

    /**
     * @return mixed
     */
    public function getMaxResults();

    /**
     * @return mixed
     */
    public function getUniqueParameterId();

    /**
     * @param array $associationMappings
     *
     * @return mixed
     */
    public function entityJoin(array $associationMappings);
}
