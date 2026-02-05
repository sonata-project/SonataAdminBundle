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

use PHPUnit\Framework\Attributes\DataProvider;
use SensioLabs\AdminBundle\Filter\Model\FilterData;
use SensioLabs\AdminBundle\Form\Type\Operator\NumberOperatorType;
use SensioLabs\AdminBundle\Datagrid\ORM\ProxyQuery;
use SensioLabs\AdminBundle\Filter\ORM\CountFilter;

final class CountFilterTest extends FilterTestCase
{
    public function testFilterEmpty(): void
    {
        $filter = new CountFilter();
        $filter->initialize('field_name', ['field_options' => ['class' => 'FooBar']]);

        $proxyQuery = new ProxyQuery($this->createQueryBuilderStub());

        $filter->filter($proxyQuery, 'alias', 'field', FilterData::fromArray([]));

        self::assertSameQuery([], $proxyQuery);
        static::assertFalse($filter->isActive());
    }

    public function testFilterInvalidOperator(): void
    {
        $filter = new CountFilter();
        $filter->initialize('field_name', ['field_options' => ['class' => 'FooBar']]);

        $proxyQuery = new ProxyQuery($this->createQueryBuilderStub());

        $filter->filter($proxyQuery, 'alias', 'field', FilterData::fromArray(['type' => 42]));

        self::assertSameQuery([], $proxyQuery);
        static::assertFalse($filter->isActive());
    }

    #[DataProvider('provideFilterCases')]
    public function testFilter(string $expected, ?int $type): void
    {
        $filter = new CountFilter();
        $filter->initialize('field_name', ['field_options' => ['class' => 'FooBar']]);

        $proxyQuery = new ProxyQuery($this->createQueryBuilderStub());

        $filter->filter($proxyQuery, 'alias', 'field', FilterData::fromArray(['type' => $type, 'value' => 42]));

        self::assertSameQuery(['GROUP BY o', $expected], $proxyQuery);
        static::assertTrue($filter->isActive());
    }

    /**
     * @phpstan-return iterable<array-key, array{string, int|null}>
     */
    public static function provideFilterCases(): iterable
    {
        yield ['HAVING COUNT(alias.field) = :field_name_0', NumberOperatorType::TYPE_EQUAL];
        yield ['HAVING COUNT(alias.field) >= :field_name_0', NumberOperatorType::TYPE_GREATER_EQUAL];
        yield ['HAVING COUNT(alias.field) > :field_name_0', NumberOperatorType::TYPE_GREATER_THAN];
        yield ['HAVING COUNT(alias.field) <= :field_name_0', NumberOperatorType::TYPE_LESS_EQUAL];
        yield ['HAVING COUNT(alias.field) < :field_name_0', NumberOperatorType::TYPE_LESS_THAN];
        yield ['HAVING COUNT(alias.field) = :field_name_0', null];
    }
}
