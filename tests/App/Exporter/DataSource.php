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

namespace SensioLabs\AdminBundle\Tests\App\Exporter;

use SensioLabs\AdminBundle\Datagrid\ProxyQueryInterface;
use SensioLabs\AdminBundle\Exporter\DataSourceInterface;
use Sonata\Exporter\Source\ArraySourceIterator;

final class DataSource implements DataSourceInterface
{
    public function createIterator(ProxyQueryInterface $query, array $fields): \Iterator
    {
        return new ArraySourceIterator([]);
    }
}
