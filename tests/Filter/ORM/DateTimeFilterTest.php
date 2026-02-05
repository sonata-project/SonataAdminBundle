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
use SensioLabs\AdminBundle\Filter\ORM\DateTimeFilter;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

/**
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
final class DateTimeFilterTest extends FilterTestCase
{
    public function testEmpty(): void
    {
        $filter = new DateTimeFilter();
        $filter->initialize('field_name', ['field_options' => ['class' => 'FooBar']]);

        $proxyQuery = new ProxyQuery($this->createQueryBuilderStub());

        $filter->filter($proxyQuery, 'alias', 'field', FilterData::fromArray(['value' => '']));

        self::assertSameQuery([], $proxyQuery);
        static::assertFalse($filter->isActive());
    }

    public function testGetType(): void
    {
        $filter = new DateTimeFilter();
        $filter->initialize('foo');

        static::assertSame(DateTimeType::class, $filter->getFieldType());
    }
}
