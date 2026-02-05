<?php

declare(strict_types=1);

/*
 * This file is part of sensiolabs-de/admin-bundle.
 *
 * (c) SensioLabs Deutschland <info@sensiolabs.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\AdminBundle\Filter;

/**
 * Interface for filters that can be chained together with OR conditions.
 */
interface ChainableFilterInterface
{
    public function setCondition(string $condition): void;

    public function getCondition(): ?string;

    public function setPreviousFilter(FilterInterface $filter): void;

    public function getPreviousFilter(): FilterInterface;

    public function hasPreviousFilter(): bool;
}
