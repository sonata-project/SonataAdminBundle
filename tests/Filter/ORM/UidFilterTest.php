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

namespace SensioLabs\AdminBundle\Tests\Filter\ORM;

use SensioLabs\AdminBundle\Filter\Model\FilterData;
use SensioLabs\AdminBundle\Datagrid\ORM\ProxyQuery;
use SensioLabs\AdminBundle\Filter\ORM\UidFilter;

final class UidFilterTest extends FilterTestCase
{
    public function testEmpty(): void
    {
        $filter = new UidFilter();
        $filter->initialize('field_name');

        $proxyQuery = new ProxyQuery($this->createQueryBuilderStub());

        $filter->filter($proxyQuery, 'alias', 'field', FilterData::fromArray([]));
        $filter->filter($proxyQuery, 'alias', 'field', FilterData::fromArray(['value' => '']));

        self::assertSameQuery([], $proxyQuery);
        static::assertFalse($filter->isActive());
    }
}
