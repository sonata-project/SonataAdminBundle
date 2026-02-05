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
use SensioLabs\AdminBundle\Filter\ORM\BooleanFilter;
use Sonata\Form\Type\BooleanType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

final class BooleanFilterTest extends FilterTestCase
{
    public function testRenderSettings(): void
    {
        $filter = new BooleanFilter();
        $filter->initialize('field_name', ['field_options' => ['class' => 'FooBar']]);
        $options = $filter->getRenderSettings()[1];

        static::assertSame(HiddenType::class, $options['operator_type']);
        static::assertSame([], $options['operator_options']);
    }

    public function testFilterEmpty(): void
    {
        $filter = new BooleanFilter();
        $filter->initialize('field_name', ['field_options' => ['class' => 'FooBar']]);

        $proxyQuery = new ProxyQuery($this->createQueryBuilderStub());

        $filter->filter($proxyQuery, 'alias', 'field', FilterData::fromArray([]));

        self::assertSameQuery([], $proxyQuery);
        static::assertFalse($filter->isActive());
    }

    public function testFilterNo(): void
    {
        $filter = new BooleanFilter();
        $filter->initialize('field_name', ['field_options' => ['class' => 'FooBar']]);

        $proxyQuery = new ProxyQuery($this->createQueryBuilderStub());

        $filter->filter($proxyQuery, 'alias', 'field', FilterData::fromArray(['type' => null, 'value' => BooleanType::TYPE_NO]));

        self::assertSameQuery(['WHERE alias.field = :field_name_0'], $proxyQuery);
        self::assertSameQueryParameters(['field_name_0' => 0], $proxyQuery);
        static::assertTrue($filter->isActive());
    }

    public function testFilterNoWithTreatNullAsTrue(): void
    {
        $filter = new BooleanFilter();
        $filter->initialize('field_name', [
            'treat_null_as' => true,
            'field_options' => ['class' => 'FooBar'],
        ]);

        $proxyQuery = new ProxyQuery($this->createQueryBuilderStub());

        $filter->filter($proxyQuery, 'alias', 'field', FilterData::fromArray(['type' => null, 'value' => BooleanType::TYPE_NO]));

        self::assertSameQuery(['WHERE alias.field = :field_name_0'], $proxyQuery);
        self::assertSameQueryParameters(['field_name_0' => 0], $proxyQuery);
        static::assertTrue($filter->isActive());
    }

    public function testFilterNoWithTreatNullAsFalse(): void
    {
        $filter = new BooleanFilter();
        $filter->initialize('field_name', [
            'treat_null_as' => false,
            'field_options' => ['class' => 'FooBar'],
        ]);

        $proxyQuery = new ProxyQuery($this->createQueryBuilderStub());

        $filter->filter($proxyQuery, 'alias', 'field', FilterData::fromArray(['type' => null, 'value' => BooleanType::TYPE_NO]));

        self::assertSameQuery(['WHERE alias.field IS NULL OR alias.field = :field_name_0'], $proxyQuery);
        self::assertSameQueryParameters(['field_name_0' => 0], $proxyQuery);
        static::assertTrue($filter->isActive());
    }

    public function testFilterYes(): void
    {
        $filter = new BooleanFilter();
        $filter->initialize('field_name', ['field_options' => ['class' => 'FooBar']]);

        $proxyQuery = new ProxyQuery($this->createQueryBuilderStub());

        $filter->filter($proxyQuery, 'alias', 'field', FilterData::fromArray(['type' => null, 'value' => BooleanType::TYPE_YES]));

        self::assertSameQuery(['WHERE alias.field = :field_name_0'], $proxyQuery);
        self::assertSameQueryParameters(['field_name_0' => 1], $proxyQuery);
        static::assertTrue($filter->isActive());
    }

    public function testFilterYesWithTreatNullAsFalse(): void
    {
        $filter = new BooleanFilter();
        $filter->initialize('field_name', [
            'treat_null_as' => false,
            'field_options' => ['class' => 'FooBar'],
        ]);

        $proxyQuery = new ProxyQuery($this->createQueryBuilderStub());

        $filter->filter($proxyQuery, 'alias', 'field', FilterData::fromArray(['type' => null, 'value' => BooleanType::TYPE_YES]));

        self::assertSameQuery(['WHERE alias.field = :field_name_0'], $proxyQuery);
        self::assertSameQueryParameters(['field_name_0' => 1], $proxyQuery);
        static::assertTrue($filter->isActive());
    }

    public function testFilterYesWithTreatNullAsTrue(): void
    {
        $filter = new BooleanFilter();
        $filter->initialize('field_name', [
            'treat_null_as' => true,
            'field_options' => ['class' => 'FooBar'],
        ]);

        $proxyQuery = new ProxyQuery($this->createQueryBuilderStub());

        $filter->filter($proxyQuery, 'alias', 'field', FilterData::fromArray(['type' => null, 'value' => BooleanType::TYPE_YES]));

        self::assertSameQuery(['WHERE alias.field IS NULL OR alias.field = :field_name_0'], $proxyQuery);
        self::assertSameQueryParameters(['field_name_0' => 1], $proxyQuery);
        static::assertTrue($filter->isActive());
    }

    public function testFilterArray(): void
    {
        $filter = new BooleanFilter();
        $filter->initialize('field_name', ['field_options' => ['class' => 'FooBar']]);

        $proxyQuery = new ProxyQuery($this->createQueryBuilderStub());

        $filter->filter($proxyQuery, 'alias', 'field', FilterData::fromArray(['type' => null, 'value' => [BooleanType::TYPE_NO]]));

        self::assertSameQuery(['WHERE alias.field IN(0)'], $proxyQuery);
        static::assertTrue($filter->isActive());
    }

    public function testFilterArrayWithTreatNullAsFalse(): void
    {
        $filter = new BooleanFilter();
        $filter->initialize('field_name', [
            'treat_null_as' => false,
            'field_options' => ['class' => 'FooBar'],
        ]);

        $proxyQuery = new ProxyQuery($this->createQueryBuilderStub());

        $filter->filter($proxyQuery, 'alias', 'field', FilterData::fromArray(['type' => null, 'value' => [BooleanType::TYPE_NO]]));

        self::assertSameQuery(['WHERE alias.field IS NULL OR alias.field IN(0)'], $proxyQuery);
        static::assertTrue($filter->isActive());
    }

    public function testFilterArrayWithTreatNullAsTrue(): void
    {
        $filter = new BooleanFilter();
        $filter->initialize('field_name', [
            'treat_null_as' => true,
            'field_options' => ['class' => 'FooBar'],
        ]);

        $proxyQuery = new ProxyQuery($this->createQueryBuilderStub());

        $filter->filter($proxyQuery, 'alias', 'field', FilterData::fromArray(['type' => null, 'value' => [BooleanType::TYPE_NO]]));

        self::assertSameQuery(['WHERE alias.field IN(0)'], $proxyQuery);
        static::assertTrue($filter->isActive());
    }
}
