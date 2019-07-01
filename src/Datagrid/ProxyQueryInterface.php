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
     */
    public function setSortBy($parentAssociationMappings, $fieldMapping): self;

    /**
     * @return mixed
     */
    public function getSortBy();

    /**
     * @param mixed $sortOrder
     */
    public function setSortOrder($sortOrder): self;

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
     */
    public function setFirstResult($firstResult): self;

    /**
     * @return mixed
     */
    public function getFirstResult();

    /**
     * @param int|null $maxResults
     */
    public function setMaxResults($maxResults): self;

    /**
     * @return mixed
     */
    public function getMaxResults();

    /**
     * @return mixed
     */
    public function getUniqueParameterId();

    /**
     * @return mixed
     */
    public function entityJoin(array $associationMappings);
}
