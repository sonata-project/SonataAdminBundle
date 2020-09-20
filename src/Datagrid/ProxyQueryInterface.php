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
     * @return string|null
     */
    public function getSortBy();

    /**
     * @param string $sortOrder
     *
     * @return ProxyQueryInterface
     */
    public function setSortOrder($sortOrder);

    /**
     * @return string|null
     */
    public function getSortOrder();

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.74, to be removed in 4.0.
     *
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
     * @return int|null
     */
    public function getMaxResults();

    /**
     * NEXT_MAJOR: Remove this method from the interface.
     *
     * @deprecated since sonata-project/admin-bundle 3.76, to be removed in 4.0.
     *
     * @return int
     */
    public function getUniqueParameterId();

    /**
     * NEXT_MAJOR: Remove this method from the interface.
     *
     * @deprecated since sonata-project/admin-bundle 3.76, to be removed in 4.0.
     *
     * @return string
     */
    public function entityJoin(array $associationMappings);
}
