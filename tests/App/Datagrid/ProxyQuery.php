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

namespace Sonata\AdminBundle\Tests\App\Datagrid;

use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;

final class ProxyQuery implements ProxyQueryInterface
{
    public function __call($name, $args)
    {
    }

    public function execute(array $params = [], $hydrationMode = null)
    {
    }

    public function setSortBy($parentAssociationMappings, $fieldMapping)
    {
    }

    public function getSortBy()
    {
    }

    public function setSortOrder($sortOrder)
    {
    }

    public function getSortOrder()
    {
    }

    public function getSingleScalarResult()
    {
    }

    public function setFirstResult($firstResult)
    {
    }

    public function getFirstResult()
    {
    }

    public function setMaxResults($maxResults)
    {
    }

    public function getMaxResults()
    {
    }

    public function getUniqueParameterId()
    {
    }

    public function entityJoin(array $associationMappings)
    {
    }
}
