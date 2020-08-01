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

    public function setSortBy($parentAssociationMappings, $fieldMapping): void
    {
    }

    public function getSortBy()
    {
        return 'e.id';
    }

    public function setSortOrder($sortOrder): void
    {
    }

    public function getSortOrder()
    {
        return 'ASC';
    }

    public function getSingleScalarResult()
    {
        return 0;
    }

    public function setFirstResult($firstResult): void
    {
    }

    public function getFirstResult()
    {
        throw new \BadMethodCallException('Not implemented.');
    }

    public function setMaxResults($maxResults): void
    {
    }

    public function getMaxResults()
    {
        return 1;
    }

    public function getUniqueParameterId()
    {
        return 1;
    }

    public function entityJoin(array $associationMappings): void
    {
    }
}
