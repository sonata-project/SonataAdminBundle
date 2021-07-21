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

namespace Sonata\AdminBundle\Search;

use Sonata\AdminBundle\Filter\FilterInterface;

/**
 * @author Javier Spagnoletti <phansys@gmail.com>
 */
interface ChainableFilterInterface
{
    public function setPreviousFilter(FilterInterface $filter): void;

    public function getPreviousFilter(): FilterInterface;

    public function hasPreviousFilter(): bool;
}
