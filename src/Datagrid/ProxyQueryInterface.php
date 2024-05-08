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
 *
 * @phpstan-template-covariant T of object
 */
interface ProxyQueryInterface
{
    /**
     * NEXT_MAJOR: Add typehint.
     *
     * @return iterable<T>
     */
    public function execute();

    /**
     * @param array<array<string, mixed>> $parentAssociationMappings
     * @param array<string, mixed>        $fieldMapping
     *
     * @return $this
     */
    public function setSortBy(array $parentAssociationMappings, array $fieldMapping): self;

    public function getSortBy(): ?string;

    /**
     * @return $this
     */
    public function setSortOrder(string $sortOrder): self;

    public function getSortOrder(): ?string;

    /**
     * @return $this
     */
    public function setFirstResult(?int $firstResult): self;

    public function getFirstResult(): ?int;

    /**
     * @return $this
     */
    public function setMaxResults(?int $maxResults): self;

    public function getMaxResults(): ?int;
}
