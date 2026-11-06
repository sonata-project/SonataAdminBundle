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
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface SearchableFilterInterface extends FilterInterface, ChainableFilterInterface
{
    /**
     * Return true if the filter should be used in the SearchHandler class.
     */
    public function isSearchEnabled(): bool;
}
