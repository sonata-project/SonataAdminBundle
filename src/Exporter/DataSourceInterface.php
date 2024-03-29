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

namespace Sonata\AdminBundle\Exporter;

use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;

interface DataSourceInterface
{
    /**
     * @param ProxyQueryInterface<object> $query
     * @param string[]                    $fields
     *
     * @return \Iterator<array<mixed>>
     */
    public function createIterator(ProxyQueryInterface $query, array $fields): \Iterator;
}
