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
use SensioLabs\AdminBundle\Form\Type\Operator\StringOperatorType;
use SensioLabs\AdminBundle\Datagrid\ORM\ProxyQuery;
use SensioLabs\AdminBundle\Filter\ORM\StringFilter;

final class StringFilterTest extends FilterTestCase
{
    public function testEmpty(): void
    {
        $filter = new StringFilter();
        $filter->initialize('field_name', ['field_options' => ['class' => 'FooBar']]);

        $proxyQuery = new ProxyQuery($this->createQueryBuilderStub());

        $filter->filter($proxyQuery, 'alias', 'field', FilterData::fromArray([]));

        self::assertSameQuery([], $proxyQuery);
        static::assertFalse($filter->isActive());
    }

    /**
     * @phpstan-return iterable<array-key, array{string|null, bool, bool}>
     */
    public static function getValues(): iterable
    {
        yield 'filter by normal value' => ['asd', false, true];
        yield 'not filter by empty string' => ['', false, false];
        yield 'filter by empty string' => ['', true, true];
        yield 'not filter by null' => [null, false, false];
        yield 'filter by null' => [null, true, true];
        yield 'filter by \'0\'' => ['0', false, true];
    }

    /**
     * @phpstan-return iterable<array-key, array{string|null, bool, bool}>
     */
    public static function getValuesForMeaningLessType(): iterable
    {
        yield 'filter by normal value' => ['asd', false, true];
        yield 'not filter by empty string' => ['', false, false];
        yield 'still not filter by empty string with allow empty' => ['', true, false];
        yield 'not filter by null' => [null, false, false];
        yield 'still not filter by null with allow empty' => [null, true, false];
        yield 'filter by \'0\'' => ['0', false, true];
    }

    #[DataProvider('getValuesForMeaningLessType')]
    public function testDefaultType(?string $value, bool $allowEmpty, bool $shouldBeActive): void
    {
        $filter = new StringFilter();
        $filter->initialize('field_name', ['allow_empty' => $allowEmpty]);

        $proxyQuery = new ProxyQuery($this->createQueryBuilderStub());
        self::assertSameQuery([], $proxyQuery);

        $filter->filter($proxyQuery, 'alias', 'field', FilterData::fromArray(['value' => $value, 'type' => null]));

        if ($shouldBeActive) {
            self::assertSameQuery(['WHERE alias.field LIKE :field_name_0'], $proxyQuery);
            self::assertSameQueryParameters(['field_name_0' => \sprintf('%%%s%%', $value ?? '')], $proxyQuery);
            static::assertTrue($filter->isActive());
        } else {
            self::assertSameQuery([], $proxyQuery);
            static::assertFalse($filter->isActive());
        }
    }

    #[DataProvider('getValuesForMeaningLessType')]
    public function testContains(?string $value, bool $allowEmpty, bool $shouldBeActive): void
    {
        $filter = new StringFilter();
        $filter->initialize('field_name', ['allow_empty' => $allowEmpty]);

        $proxyQuery = new ProxyQuery($this->createQueryBuilderStub());
        self::assertSameQuery([], $proxyQuery);

        $filter->filter($proxyQuery, 'alias', 'field', FilterData::fromArray(['value' => $value, 'type' => StringOperatorType::TYPE_CONTAINS]));

        if ($shouldBeActive) {
            self::assertSameQuery(['WHERE alias.field LIKE :field_name_0'], $proxyQuery);
            self::assertSameQueryParameters(['field_name_0' => \sprintf('%%%s%%', $value ?? '')], $proxyQuery);
            static::assertTrue($filter->isActive());
        } else {
            self::assertSameQuery([], $proxyQuery);
            static::assertFalse($filter->isActive());
        }
    }

    #[DataProvider('getValuesForMeaningLessType')]
    public function testStartsWith(?string $value, bool $allowEmpty, bool $shouldBeActive): void
    {
        $filter = new StringFilter();
        $filter->initialize('field_name', ['allow_empty' => $allowEmpty]);

        $proxyQuery = new ProxyQuery($this->createQueryBuilderStub());
        self::assertSameQuery([], $proxyQuery);

        $filter->filter($proxyQuery, 'alias', 'field', FilterData::fromArray(['value' => $value, 'type' => StringOperatorType::TYPE_STARTS_WITH]));

        if ($shouldBeActive) {
            self::assertSameQuery(['WHERE alias.field LIKE :field_name_0'], $proxyQuery);
            self::assertSameQueryParameters(['field_name_0' => \sprintf('%s%%', $value ?? '')], $proxyQuery);
            static::assertTrue($filter->isActive());
        } else {
            self::assertSameQuery([], $proxyQuery);
            static::assertFalse($filter->isActive());
        }
    }

    #[DataProvider('getValuesForMeaningLessType')]
    public function testEndsWith(?string $value, bool $allowEmpty, bool $shouldBeActive): void
    {
        $filter = new StringFilter();
        $filter->initialize('field_name', ['allow_empty' => $allowEmpty]);

        $proxyQuery = new ProxyQuery($this->createQueryBuilderStub());
        self::assertSameQuery([], $proxyQuery);

        $filter->filter($proxyQuery, 'alias', 'field', FilterData::fromArray(['value' => $value, 'type' => StringOperatorType::TYPE_ENDS_WITH]));

        if ($shouldBeActive) {
            self::assertSameQuery(['WHERE alias.field LIKE :field_name_0'], $proxyQuery);
            self::assertSameQueryParameters(['field_name_0' => \sprintf('%%%s', $value ?? '')], $proxyQuery);
            static::assertTrue($filter->isActive());
        } else {
            self::assertSameQuery([], $proxyQuery);
            static::assertFalse($filter->isActive());
        }
    }

    #[DataProvider('getValuesForMeaningLessType')]
    public function testNotContains(?string $value, bool $allowEmpty, bool $shouldBeActive): void
    {
        $filter = new StringFilter();
        $filter->initialize('field_name', ['allow_empty' => $allowEmpty]);

        $proxyQuery = new ProxyQuery($this->createQueryBuilderStub());
        self::assertSameQuery([], $proxyQuery);

        $filter->filter($proxyQuery, 'alias', 'field', FilterData::fromArray(['value' => $value, 'type' => StringOperatorType::TYPE_NOT_CONTAINS]));

        if ($shouldBeActive) {
            self::assertSameQuery(['WHERE alias.field NOT LIKE :field_name_0 OR alias.field IS NULL'], $proxyQuery);
            self::assertSameQueryParameters(['field_name_0' => \sprintf('%%%s%%', $value ?? '')], $proxyQuery);
            static::assertTrue($filter->isActive());
        } else {
            self::assertSameQuery([], $proxyQuery);
            static::assertFalse($filter->isActive());
        }
    }

    #[DataProvider('getValues')]
    public function testEquals(?string $value, bool $allowEmpty, bool $shouldBeActive): void
    {
        $filter = new StringFilter();
        $filter->initialize('field_name', ['allow_empty' => $allowEmpty]);

        $proxyQuery = new ProxyQuery($this->createQueryBuilderStub());
        self::assertSameQuery([], $proxyQuery);

        $filter->filter($proxyQuery, 'alias', 'field', FilterData::fromArray(['value' => $value, 'type' => StringOperatorType::TYPE_EQUAL]));

        if ($shouldBeActive) {
            self::assertSameQuery(['WHERE alias.field = :field_name_0'], $proxyQuery);
            self::assertSameQueryParameters(['field_name_0' => $value ?? ''], $proxyQuery);
            static::assertTrue($filter->isActive());
        } else {
            self::assertSameQuery([], $proxyQuery);
            static::assertFalse($filter->isActive());
        }
    }

    #[DataProvider('getValues')]
    public function testNotEquals(?string $value, bool $allowEmpty, bool $shouldBeActive): void
    {
        $filter = new StringFilter();
        $filter->initialize('field_name', ['allow_empty' => $allowEmpty]);

        $proxyQuery = new ProxyQuery($this->createQueryBuilderStub());
        self::assertSameQuery([], $proxyQuery);

        $filter->filter($proxyQuery, 'alias', 'field', FilterData::fromArray(['value' => $value, 'type' => StringOperatorType::TYPE_NOT_EQUAL]));

        if ($shouldBeActive) {
            self::assertSameQuery(['WHERE alias.field <> :field_name_0 OR alias.field IS NULL'], $proxyQuery);
            self::assertSameQueryParameters(['field_name_0' => $value ?? ''], $proxyQuery);
            static::assertTrue($filter->isActive());
        } else {
            self::assertSameQuery([], $proxyQuery);
            static::assertFalse($filter->isActive());
        }
    }

    public function testEqualsWithValidParentAssociationMappings(): void
    {
        $filter = new StringFilter();
        $filter->initialize('field_name', [
            'field_name' => 'field_name',
            'parent_association_mappings' => [
                [
                    'fieldName' => 'association_mapping',
                ],
                [
                    'fieldName' => 'sub_association_mapping',
                ],
                [
                    'fieldName' => 'sub_sub_association_mapping',
                ],
            ],
        ]);

        $proxyQuery = new ProxyQuery($this->createQueryBuilderStub());
        self::assertSameQuery([], $proxyQuery);

        $filter->apply($proxyQuery, FilterData::fromArray(['type' => StringOperatorType::TYPE_EQUAL, 'value' => 'asd']));

        self::assertSameQuery([
            'LEFT JOIN o.association_mapping AS s_association_mapping',
            'LEFT JOIN s_association_mapping.sub_association_mapping AS s_association_mapping_sub_association_mapping',
            'LEFT JOIN s_association_mapping_sub_association_mapping.sub_sub_association_mapping AS s_association_mapping_sub_association_mapping_sub_sub_association_mapping',
            'WHERE s_association_mapping_sub_association_mapping_sub_sub_association_mapping.field_name = :field_name_0',
        ], $proxyQuery);
        self::assertSameQueryParameters(['field_name_0' => 'asd'], $proxyQuery);
        static::assertTrue($filter->isActive());
    }

    /**
     * @param array<string, mixed> $options
     */
    #[DataProvider('provideCaseSensitiveCases')]
    public function testCaseSensitive(array $options, int $operatorType, string $expectedQuery, string $expectedParameter): void
    {
        $filter = new StringFilter();
        $filter->initialize('field_name', $options);

        $proxyQuery = new ProxyQuery($this->createQueryBuilderStub());
        self::assertSameQuery([], $proxyQuery);

        $filter->filter($proxyQuery, 'alias', 'field', FilterData::fromArray(['value' => 'FooBar', 'type' => $operatorType]));
        self::assertSameQuery([$expectedQuery], $proxyQuery);
        self::assertSameQueryParameters(['field_name_0' => $expectedParameter], $proxyQuery);
        static::assertTrue($filter->isActive());
    }

    /**
     * @phpstan-return iterable<array-key, array{array{force_case_insensitivity?: bool|null}, int, string, string}>
     */
    public static function provideCaseSensitiveCases(): iterable
    {
        yield [[], StringOperatorType::TYPE_CONTAINS, 'WHERE alias.field LIKE :field_name_0', '%FooBar%'];
        yield [['force_case_insensitivity' => false], StringOperatorType::TYPE_CONTAINS, 'WHERE alias.field LIKE :field_name_0', '%FooBar%'];
        yield [['force_case_insensitivity' => false], StringOperatorType::TYPE_NOT_CONTAINS, 'WHERE alias.field NOT LIKE :field_name_0 OR alias.field IS NULL', '%FooBar%'];
        yield [['force_case_insensitivity' => false], StringOperatorType::TYPE_EQUAL, 'WHERE alias.field = :field_name_0', 'FooBar'];
        yield [['force_case_insensitivity' => false], StringOperatorType::TYPE_NOT_EQUAL, 'WHERE alias.field <> :field_name_0 OR alias.field IS NULL', 'FooBar'];
        yield [['force_case_insensitivity' => false], StringOperatorType::TYPE_STARTS_WITH, 'WHERE alias.field LIKE :field_name_0', 'FooBar%'];
        yield [['force_case_insensitivity' => false], StringOperatorType::TYPE_ENDS_WITH, 'WHERE alias.field LIKE :field_name_0', '%FooBar'];
        yield [['force_case_insensitivity' => true], StringOperatorType::TYPE_CONTAINS, 'WHERE LOWER(alias.field) LIKE :field_name_0', '%foobar%'];
        yield [['force_case_insensitivity' => true], StringOperatorType::TYPE_NOT_CONTAINS, 'WHERE LOWER(alias.field) NOT LIKE :field_name_0 OR alias.field IS NULL', '%foobar%'];
        yield [['force_case_insensitivity' => true], StringOperatorType::TYPE_EQUAL, 'WHERE LOWER(alias.field) = :field_name_0', 'foobar'];
        yield [['force_case_insensitivity' => true], StringOperatorType::TYPE_NOT_EQUAL, 'WHERE LOWER(alias.field) <> :field_name_0 OR alias.field IS NULL', 'foobar'];
        yield [['force_case_insensitivity' => true], StringOperatorType::TYPE_STARTS_WITH, 'WHERE LOWER(alias.field) LIKE :field_name_0', 'foobar%'];
        yield [['force_case_insensitivity' => true], StringOperatorType::TYPE_ENDS_WITH, 'WHERE LOWER(alias.field) LIKE :field_name_0', '%foobar'];
    }
}
