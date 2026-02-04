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

namespace SensioLabs\AdminBundle\Search;

use SensioLabs\AdminBundle\Filter\FilterInterface;

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
