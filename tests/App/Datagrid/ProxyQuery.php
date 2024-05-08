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

/**
 * @phpstan-template T of object
 * @phpstan-implements ProxyQueryInterface<T>
 */
final class ProxyQuery implements ProxyQueryInterface
{
    public function execute(): iterable
    {
        throw new \BadMethodCallException('Not implemented.');
    }

    public function setSortBy(array $parentAssociationMappings, array $fieldMapping): self
    {
        throw new \BadMethodCallException('Not implemented.');
    }

    public function getSortBy(): string
    {
        return 'e.id';
    }

    public function setSortOrder(string $sortOrder): self
    {
        throw new \BadMethodCallException('Not implemented.');
    }

    public function getSortOrder(): string
    {
        return 'ASC';
    }

    public function setFirstResult(?int $firstResult): self
    {
        if (null === $firstResult) {
            return $this;
        }

        throw new \BadMethodCallException('Not implemented.');
    }

    public function getFirstResult(): ?int
    {
        throw new \BadMethodCallException('Not implemented.');
    }

    public function setMaxResults(?int $maxResults): self
    {
        if (null === $maxResults) {
            return $this;
        }

        throw new \BadMethodCallException('Not implemented.');
    }

    public function getMaxResults(): int
    {
        return 1;
    }
}
