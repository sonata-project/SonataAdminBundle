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
use SensioLabs\AdminBundle\Form\Type\Operator\ContainsOperatorType;
use SensioLabs\AdminBundle\Datagrid\ORM\ProxyQuery;
use SensioLabs\AdminBundle\Filter\ORM\StringListFilter;

final class StringListFilterTest extends FilterTestCase
{
    public function testItStaysDisabledWhenFilteringWithAnEmptyValue(): void
    {
        $filter = new StringListFilter();
        $filter->initialize('field_name', ['field_options' => ['class' => 'FooBar']]);

        $proxyQuery = new ProxyQuery($this->createQueryBuilderStub());

        $filter->filter($proxyQuery, 'alias', 'field', FilterData::fromArray([]));

        self::assertSameQuery([], $proxyQuery);
        static::assertFalse($filter->isActive());
    }

    public function testFilteringWithNullReturnsArraysThatContainNull(): void
    {
        $filter = new StringListFilter();
        $filter->initialize('field_name');

        $proxyQuery = new ProxyQuery($this->createQueryBuilderStub());
        self::assertSameQuery([], $proxyQuery);

        $filter->filter($proxyQuery, 'alias', 'field', FilterData::fromArray(['value' => null, 'type' => null]));
        self::assertSameQuery(['WHERE alias.field LIKE :field_name_0'], $proxyQuery);
        self::assertSameQueryParameters(['field_name_0' => '%N;%'], $proxyQuery);
        static::assertTrue($filter->isActive());
    }

    #[DataProvider('provideContainsCases')]
    public function testContains(?int $type): void
    {
        $filter = new StringListFilter();
        $filter->initialize('field_name');

        $proxyQuery = new ProxyQuery($this->createQueryBuilderStub());
        self::assertSameQuery([], $proxyQuery);

        $filter->filter($proxyQuery, 'alias', 'field', FilterData::fromArray(['value' => 'asd', 'type' => $type]));
        self::assertSameQuery(['WHERE alias.field LIKE :field_name_0'], $proxyQuery);
        self::assertSameQueryParameters(['field_name_0' => '%s:3:"asd";%'], $proxyQuery);
        static::assertTrue($filter->isActive());
    }

    /**
     * @phpstan-return iterable<array-key, array{int|null}>
     */
    public static function provideContainsCases(): iterable
    {
        yield 'explicit contains' => [ContainsOperatorType::TYPE_CONTAINS];
        yield 'implicit contains' => [null];
    }

    public function testNotContains(): void
    {
        $filter = new StringListFilter();
        $filter->initialize('field_name');

        $proxyQuery = new ProxyQuery($this->createQueryBuilderStub());
        self::assertSameQuery([], $proxyQuery);

        $filter->filter($proxyQuery, 'alias', 'field', FilterData::fromArray(['value' => 'asd', 'type' => ContainsOperatorType::TYPE_NOT_CONTAINS]));
        self::assertSameQuery(['WHERE alias.field NOT LIKE :field_name_0'], $proxyQuery);
        self::assertSameQueryParameters(['field_name_0' => '%s:3:"asd";%'], $proxyQuery);
        static::assertTrue($filter->isActive());
    }

    public function testEquals(): void
    {
        $filter = new StringListFilter();
        $filter->initialize('field_name');

        $proxyQuery = new ProxyQuery($this->createQueryBuilderStub());
        self::assertSameQuery([], $proxyQuery);

        $filter->filter($proxyQuery, 'alias', 'field', FilterData::fromArray(['value' => 'asd', 'type' => ContainsOperatorType::TYPE_EQUAL]));
        self::assertSameQuery(['WHERE alias.field LIKE :field_name_0 AND alias.field LIKE \'a:1:%\''], $proxyQuery);
        self::assertSameQueryParameters(['field_name_0' => '%s:3:"asd";%'], $proxyQuery);
        static::assertTrue($filter->isActive());
    }

    /**
     * @param array<string>         $value
     * @param array<string>         $query
     * @param array<string, string> $parameters
     */
    #[DataProvider('provideMultipleValuesCases')]
    public function testMultipleValues(array $value, ?int $type, array $query, array $parameters): void
    {
        $filter = new StringListFilter();
        $filter->initialize('field_name');

        $proxyQuery = new ProxyQuery($this->createQueryBuilderStub());
        self::assertSameQuery([], $proxyQuery);

        $filter->filter($proxyQuery, 'alias', 'field', FilterData::fromArray(['value' => $value, 'type' => $type]));
        self::assertSameQuery($query, $proxyQuery);
        self::assertSameQueryParameters($parameters, $proxyQuery);
        static::assertTrue($filter->isActive());
    }

    /**
     * @phpstan-return iterable<array-key, array{string[], int, string[], array<string, string>}>
     */
    public static function provideMultipleValuesCases(): iterable
    {
        yield 'equal choice' => [
            ['asd', 'qwe'],
            ContainsOperatorType::TYPE_EQUAL,
            ["WHERE alias.field LIKE :field_name_0 AND alias.field LIKE :field_name_1 AND alias.field LIKE 'a:2:%'"],
            [
                'field_name_0' => '%s:3:"asd";%',
                'field_name_1' => '%s:3:"qwe";%',
            ],
        ];

        yield 'contains choice' => [
            ['asd', 'qwe'],
            ContainsOperatorType::TYPE_CONTAINS,
            ['WHERE alias.field LIKE :field_name_0 AND alias.field LIKE :field_name_1'],
            [
                'field_name_0' => '%s:3:"asd";%',
                'field_name_1' => '%s:3:"qwe";%',
            ],
        ];

        yield 'not contains choice' => [
            ['asd', 'qwe'],
            ContainsOperatorType::TYPE_NOT_CONTAINS,
            ['WHERE alias.field NOT LIKE :field_name_0 AND alias.field NOT LIKE :field_name_1'],
            [
                'field_name_0' => '%s:3:"asd";%',
                'field_name_1' => '%s:3:"qwe";%',
            ],
        ];
    }
}
