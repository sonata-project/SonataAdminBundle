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

use Sonata\AdminBundle\FieldDescription\Mapping;

/**
 * Used by the Datagrid to build the query.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface ProxyQueryInterface
{
    /**
     * @return array<object>|\Traversable<object>
     */
    public function execute();

    /**
     * @param Mapping[] $parentAssociationMappings
     * @param Mapping   $fieldMapping
     *
     * @return static
     */
    public function setSortBy(array $parentAssociationMappings, Mapping $fieldMapping): self;

    public function getSortBy(): ?string;

    /**
     * @return static
     */
    public function setSortOrder(string $sortOrder): self;

    public function getSortOrder(): ?string;

    /**
     * @return static
     */
    public function setFirstResult(?int $firstResult): self;

    public function getFirstResult(): ?int;

    /**
     * @return static
     */
    public function setMaxResults(?int $maxResults): self;

    public function getMaxResults(): ?int;
}
