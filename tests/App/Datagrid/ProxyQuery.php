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
        throw new \BadMethodCallException('Not implemented.');
    }

    public function setSortBy($parentAssociationMappings, $fieldMapping): ProxyQueryInterface
    {
        throw new \BadMethodCallException('Not implemented.');
    }

    public function getSortBy(): string
    {
        return 'e.id';
    }

    public function setSortOrder($sortOrder): ProxyQueryInterface
    {
        throw new \BadMethodCallException('Not implemented.');
    }

    public function getSortOrder(): string
    {
        return 'ASC';
    }

    /**
     * NEXT_MAJOR: Remove this method.
     */
    public function getSingleScalarResult()
    {
        return 0;
    }

    public function setFirstResult($firstResult): ProxyQueryInterface
    {
        throw new \BadMethodCallException('Not implemented.');
    }

    public function getFirstResult(): ?int
    {
        throw new \BadMethodCallException('Not implemented.');
    }

    public function setMaxResults($maxResults): ProxyQueryInterface
    {
        throw new \BadMethodCallException('Not implemented.');
    }

    public function getMaxResults(): ?int
    {
        return 1;
    }

    /**
     * NEXT_MAJOR: Remove this method.
     */
    public function getUniqueParameterId(): int
    {
        return 1;
    }

    /**
     * NEXT_MAJOR: Remove this method.
     */
    public function entityJoin(array $associationMappings): array
    {
        throw new \BadMethodCallException('Not implemented.');
    }
}
