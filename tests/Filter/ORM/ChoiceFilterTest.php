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
use SensioLabs\AdminBundle\Form\Type\Operator\EqualOperatorType;
use SensioLabs\AdminBundle\Datagrid\ORM\ProxyQuery;
use SensioLabs\AdminBundle\Filter\ORM\ChoiceFilter;

final class ChoiceFilterTest extends FilterTestCase
{
    public function testFilterEmpty(): void
    {
        $filter = new ChoiceFilter();
        $filter->initialize('field_name', ['field_options' => ['class' => 'FooBar']]);

        $proxyQuery = new ProxyQuery($this->createQueryBuilderStub());

        $filter->filter($proxyQuery, 'alias', 'field', FilterData::fromArray([]));

        self::assertSameQuery([], $proxyQuery);
        static::assertFalse($filter->isActive());
    }

    public function testFilterArrayEqual(): void
    {
        $filter = new ChoiceFilter();
        $filter->initialize('field_name', ['field_options' => ['class' => 'FooBar']]);

        $proxyQuery = new ProxyQuery($this->createQueryBuilderStub());

        $filter->filter($proxyQuery, 'alias', 'field', FilterData::fromArray(['type' => EqualOperatorType::TYPE_EQUAL, 'value' => ['1', '2']]));

        self::assertSameQuery(['WHERE alias.field IN(:field_name_0)'], $proxyQuery);
        self::assertSameQueryParameters(['field_name_0' => ['1', '2']], $proxyQuery);
        static::assertTrue($filter->isActive());
    }

    public function testFilterArrayNotEqual(): void
    {
        $filter = new ChoiceFilter();
        $filter->initialize('field_name', ['field_options' => ['class' => 'FooBar']]);

        $proxyQuery = new ProxyQuery($this->createQueryBuilderStub());

        $filter->filter($proxyQuery, 'alias', 'field', FilterData::fromArray(['type' => EqualOperatorType::TYPE_NOT_EQUAL, 'value' => ['1', '2']]));

        self::assertSameQuery(['WHERE alias.field NOT IN(:field_name_0) OR alias.field IS NULL'], $proxyQuery);
        self::assertSameQueryParameters(['field_name_0' => ['1', '2']], $proxyQuery);
        static::assertTrue($filter->isActive());
    }

    public function testFilterArrayEqualWithNullValue(): void
    {
        $filter = new ChoiceFilter();
        $filter->initialize('field_name', ['field_options' => ['class' => 'FooBar']]);

        $proxyQuery = new ProxyQuery($this->createQueryBuilderStub());

        $filter->filter($proxyQuery, 'alias', 'field', FilterData::fromArray(['type' => EqualOperatorType::TYPE_EQUAL, 'value' => ['1', null]]));

        self::assertSameQuery(['WHERE alias.field IN(:field_name_0) OR alias.field IS NULL'], $proxyQuery);
        self::assertSameQueryParameters(['field_name_0' => ['1', null]], $proxyQuery);
        static::assertTrue($filter->isActive());
    }

    public function testFilterArrayNotEqualWithNullValue(): void
    {
        $filter = new ChoiceFilter();
        $filter->initialize('field_name', ['field_options' => ['class' => 'FooBar']]);

        $proxyQuery = new ProxyQuery($this->createQueryBuilderStub());

        $filter->filter($proxyQuery, 'alias', 'field', FilterData::fromArray(['type' => EqualOperatorType::TYPE_NOT_EQUAL, 'value' => ['1', null]]));

        self::assertSameQuery(['WHERE alias.field NOT IN(:field_name_0)'], $proxyQuery);
        self::assertSameQueryParameters(['field_name_0' => ['1', null]], $proxyQuery);
        static::assertTrue($filter->isActive());
    }

    public function testFilterEqualScalar(): void
    {
        $filter = new ChoiceFilter();
        $filter->initialize('field_name', ['field_options' => ['class' => 'FooBar']]);

        $proxyQuery = new ProxyQuery($this->createQueryBuilderStub());

        $filter->filter($proxyQuery, 'alias', 'field', FilterData::fromArray(['type' => EqualOperatorType::TYPE_EQUAL, 'value' => '1']));

        self::assertSameQuery(['WHERE alias.field = :field_name_0'], $proxyQuery);
        self::assertSameQueryParameters(['field_name_0' => '1'], $proxyQuery);
        static::assertTrue($filter->isActive());
    }

    public function testFilterNotEqualScalar(): void
    {
        $filter = new ChoiceFilter();
        $filter->initialize('field_name', ['field_options' => ['class' => 'FooBar']]);

        $proxyQuery = new ProxyQuery($this->createQueryBuilderStub());

        $filter->filter($proxyQuery, 'alias', 'field', FilterData::fromArray(['type' => EqualOperatorType::TYPE_NOT_EQUAL, 'value' => '1']));

        self::assertSameQuery(['WHERE alias.field != :field_name_0 OR alias.field IS NULL'], $proxyQuery);
        self::assertSameQueryParameters(['field_name_0' => '1'], $proxyQuery);
        static::assertTrue($filter->isActive());
    }

    public function testFilterEqualNull(): void
    {
        $filter = new ChoiceFilter();
        $filter->initialize('field_name', ['field_options' => ['class' => 'FooBar']]);

        $proxyQuery = new ProxyQuery($this->createQueryBuilderStub());

        $filter->filter($proxyQuery, 'alias', 'field', FilterData::fromArray(['type' => EqualOperatorType::TYPE_EQUAL, 'value' => null]));

        self::assertSameQuery(['WHERE alias.field IS NULL'], $proxyQuery);
        self::assertSameQueryParameters([], $proxyQuery);
        static::assertTrue($filter->isActive());
    }

    public function testFilterNotEqualNull(): void
    {
        $filter = new ChoiceFilter();
        $filter->initialize('field_name', ['field_options' => ['class' => 'FooBar']]);

        $proxyQuery = new ProxyQuery($this->createQueryBuilderStub());

        $filter->filter($proxyQuery, 'alias', 'field', FilterData::fromArray(['type' => EqualOperatorType::TYPE_NOT_EQUAL, 'value' => null]));

        self::assertSameQuery(['WHERE alias.field IS NOT NULL'], $proxyQuery);
        self::assertSameQueryParameters([], $proxyQuery);
        static::assertTrue($filter->isActive());
    }

    public function testFilterZero(): void
    {
        $filter = new ChoiceFilter();
        $filter->initialize('field_name', ['field_options' => ['class' => 'FooBar']]);

        $proxyQuery = new ProxyQuery($this->createQueryBuilderStub());

        $filter->filter($proxyQuery, 'alias', 'field', FilterData::fromArray(['type' => EqualOperatorType::TYPE_EQUAL, 'value' => 0]));

        self::assertSameQuery(['WHERE alias.field = :field_name_0'], $proxyQuery);
        self::assertSameQueryParameters(['field_name_0' => 0], $proxyQuery);
        static::assertTrue($filter->isActive());
    }
}
