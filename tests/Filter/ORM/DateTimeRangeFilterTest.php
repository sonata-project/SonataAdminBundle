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
use SensioLabs\AdminBundle\Form\Type\Operator\DateRangeOperatorType;
use SensioLabs\AdminBundle\Datagrid\ORM\ProxyQuery;
use SensioLabs\AdminBundle\Filter\ORM\DateTimeRangeFilter;
use Sonata\Form\Type\DateTimeRangeType;

/**
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
final class DateTimeRangeFilterTest extends FilterTestCase
{
    public function testEmpty(): void
    {
        $filter = new DateTimeRangeFilter();
        $filter->initialize('field_name', ['field_options' => ['class' => 'FooBar']]);

        $proxyQuery = new ProxyQuery($this->createQueryBuilderStub());

        $filter->filter($proxyQuery, 'alias', 'field', FilterData::fromArray([]));

        self::assertSameQuery([], $proxyQuery);
        static::assertFalse($filter->isActive());
    }

    public function testGetType(): void
    {
        $filter = new DateTimeRangeFilter();
        $filter->initialize('foo');

        static::assertSame(DateTimeRangeType::class, $filter->getFieldType());
    }

    public function testFilterNotBetweenStartDate(): void
    {
        $filter = new DateTimeRangeFilter();
        $filter->initialize('field_name', ['field_options' => ['class' => 'FooBar']]);

        $proxyQuery = new ProxyQuery($this->createQueryBuilderStub());

        $startDateTime = new \DateTime('2023-10-03T12:00:01');

        $filter->filter($proxyQuery, 'alias', 'field', FilterData::fromArray([
            'type' => DateRangeOperatorType::TYPE_NOT_BETWEEN,
            'value' => [
                'start' => $startDateTime,
                'end' => null,
            ],
        ]));

        self::assertSameQuery(['WHERE alias.field < :field_name_0'], $proxyQuery);
        self::assertSameQueryParameters(['field_name_0' => $startDateTime], $proxyQuery);
        static::assertTrue($filter->isActive());
    }

    public function testFilterNotBetweenEndDate(): void
    {
        $filter = new DateTimeRangeFilter();
        $filter->initialize('field_name', ['field_options' => ['class' => 'FooBar']]);

        $proxyQuery = new ProxyQuery($this->createQueryBuilderStub());

        $endDateTime = new \DateTime('2023-10-03T12:00:01');

        $filter->filter($proxyQuery, 'alias', 'field', FilterData::fromArray([
            'type' => DateRangeOperatorType::TYPE_NOT_BETWEEN,
            'value' => [
                'start' => null,
                'end' => $endDateTime,
            ],
        ]));

        self::assertSameQuery(['WHERE alias.field > :field_name_1'], $proxyQuery);
        self::assertSameQueryParameters(['field_name_1' => $endDateTime], $proxyQuery);
        static::assertTrue($filter->isActive());
    }

    public function testFilterNotBetweenStartAndEndDate(): void
    {
        $filter = new DateTimeRangeFilter();
        $filter->initialize('field_name', ['field_options' => ['class' => 'FooBar']]);

        $proxyQuery = new ProxyQuery($this->createQueryBuilderStub());

        $startDateTime = new \DateTime('2023-10-03T11:00:01');
        $endDateTime = new \DateTime('2023-10-03T12:00:01');

        $filter->filter($proxyQuery, 'alias', 'field', FilterData::fromArray([
            'type' => DateRangeOperatorType::TYPE_NOT_BETWEEN,
            'value' => [
                'start' => $startDateTime,
                'end' => $endDateTime,
            ],
        ]));

        self::assertSameQuery(['WHERE alias.field < :field_name_0 OR alias.field > :field_name_1'], $proxyQuery);
        self::assertSameQueryParameters(['field_name_0' => $startDateTime, 'field_name_1' => $endDateTime], $proxyQuery);
        static::assertTrue($filter->isActive());
    }
}
