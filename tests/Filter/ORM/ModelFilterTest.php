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

use Doctrine\ORM\Mapping\ClassMetadata;
use SensioLabs\AdminBundle\Filter\Model\FilterData;
use SensioLabs\AdminBundle\Form\Type\Operator\EqualOperatorType;
use SensioLabs\AdminBundle\Datagrid\ORM\ProxyQuery;
use SensioLabs\AdminBundle\Filter\ORM\ModelFilter;

final class ModelFilterTest extends FilterTestCase
{
    public function testFilterEmpty(): void
    {
        $filter = new ModelFilter();
        $filter->initialize('field_name', ['field_options' => ['class' => 'FooBar']]);

        $proxyQuery = new ProxyQuery($this->createQueryBuilderStub());

        $filter->filter($proxyQuery, 'alias', 'field', FilterData::fromArray([]));

        self::assertSameQuery([], $proxyQuery);
        static::assertFalse($filter->isActive());
    }

    public function testFilterArray(): void
    {
        $filter = new ModelFilter();
        $filter->initialize('field_name', ['field_options' => ['class' => 'FooBar']]);

        $proxyQuery = new ProxyQuery($this->createQueryBuilderStub());

        $objects = [new \stdClass(), new \stdClass()];
        $filter->filter($proxyQuery, 'alias', 'field', FilterData::fromArray([
            'type' => EqualOperatorType::TYPE_EQUAL,
            'value' => $objects,
        ]));

        // the alias is now computer by the entityJoin method
        self::assertSameQuery(['WHERE alias.id = :field_name_0 OR alias.id = :field_name_1'], $proxyQuery);
        self::assertSameQueryParameters(['field_name_0' => $objects[0], 'field_name_1' => $objects[1]], $proxyQuery);
        static::assertTrue($filter->isActive());
    }

    public function testFilterNonObject(): void
    {
        $filter = new ModelFilter();
        $filter->initialize('field_name', ['field_options' => ['class' => 'FooBar']]);

        $proxyQuery = new ProxyQuery($this->createQueryBuilderStub());

        $filter->filter($proxyQuery, 'alias', 'field', FilterData::fromArray([
            'type' => EqualOperatorType::TYPE_EQUAL,
            'value' => null,
        ]));

        // the alias is now computer by the entityJoin method
        self::assertSameQuery([], $proxyQuery);
        self::assertSameQueryParameters([], $proxyQuery);
        static::assertFalse($filter->isActive());
    }

    public function testFilterArrayTypeIsNotEqual(): void
    {
        $filter = new ModelFilter();
        $filter->initialize('field_name', ['field_options' => ['class' => 'FooBar'], 'field_name' => 'field_name', 'association_mapping' => ['type' => ClassMetadata::MANY_TO_ONE]]);

        $proxyQuery = new ProxyQuery($this->createQueryBuilderStub());

        $objects = [new \stdClass(), new \stdClass()];
        $filter->filter($proxyQuery, 'alias', 'field', FilterData::fromArray([
            'type' => EqualOperatorType::TYPE_NOT_EQUAL,
            'value' => $objects,
        ]));

        $rootAlias = current($proxyQuery->getRootAliases());
        static::assertNotFalse($rootAlias);
        // the alias is now computer by the entityJoin method
        self::assertSameQuery(
            ['WHERE (NOT(alias.id = :field_name_0 OR alias.id = :field_name_1)) OR IDENTITY('.$rootAlias.'.field_name) IS NULL'],
            $proxyQuery
        );
        self::assertSameQueryParameters(['field_name_0' => $objects[0], 'field_name_1' => $objects[1]], $proxyQuery);
        static::assertTrue($filter->isActive());
    }

    public function testFilterScalar(): void
    {
        $filter = new ModelFilter();
        $filter->initialize('field_name', ['field_options' => ['class' => 'FooBar']]);

        $proxyQuery = new ProxyQuery($this->createQueryBuilderStub());

        $object = new \stdClass();
        $filter->filter($proxyQuery, 'alias', 'field', FilterData::fromArray(['type' => EqualOperatorType::TYPE_EQUAL, 'value' => $object]));

        self::assertSameQuery(['WHERE alias.id = :field_name_0'], $proxyQuery);
        self::assertSameQueryParameters(['field_name_0' => $object], $proxyQuery);
        static::assertTrue($filter->isActive());
    }

    public function testFilterScalarTypeIsNotEqual(): void
    {
        $filter = new ModelFilter();
        $filter->initialize('field_name', ['field_options' => ['class' => 'FooBar'], 'field_name' => 'field_name', 'association_mapping' => ['type' => ClassMetadata::MANY_TO_ONE]]);

        $proxyQuery = new ProxyQuery($this->createQueryBuilderStub());

        $object = new \stdClass();
        $filter->filter($proxyQuery, 'alias', 'field', FilterData::fromArray(['type' => EqualOperatorType::TYPE_NOT_EQUAL, 'value' => $object]));

        $rootAlias = current($proxyQuery->getRootAliases());
        static::assertNotFalse($rootAlias);
        self::assertSameQuery(
            ['WHERE NOT(alias.id = :field_name_0) OR IDENTITY('.$rootAlias.'.field_name) IS NULL'],
            $proxyQuery
        );

        self::assertSameQueryParameters(['field_name_0' => $object], $proxyQuery);
        static::assertTrue($filter->isActive());
    }

    public function testFilterManyToManyIsNotEqual(): void
    {
        $filter = new ModelFilter();
        $filter->initialize('field_name', ['field_options' => ['class' => 'FooBar'], 'field_name' => 'field_name', 'association_mapping' => ['type' => ClassMetadata::MANY_TO_MANY]]);

        $proxyQuery = new ProxyQuery($this->createQueryBuilderStub());

        $filter->filter($proxyQuery, 'alias', 'field', FilterData::fromArray(['type' => EqualOperatorType::TYPE_NOT_EQUAL, 'value' => new \stdClass()]));

        $rootAlias = current($proxyQuery->getRootAliases());
        static::assertNotFalse($rootAlias);
        self::assertSameQuery(
            ['WHERE NOT(alias.id = :field_name_0) OR '.$rootAlias.'.field_name IS EMPTY'],
            $proxyQuery
        );
    }

    public function testAssociationWithInvalidMapping(): void
    {
        $filter = new ModelFilter();
        $filter->initialize('field_name', ['mapping_type' => 'foo']);

        $proxyQuery = new ProxyQuery($this->createQueryBuilderStub());

        $this->expectException(\RuntimeException::class);

        $filter->apply($proxyQuery, FilterData::fromArray(['value' => new \stdClass()]));
    }

    public function testAssociationWithValidMappingAndEmptyFieldName(): void
    {
        $this->expectException(\RuntimeException::class);

        $filter = new ModelFilter();
        $filter->initialize('field_name', ['mapping_type' => ClassMetadata::ONE_TO_ONE]);

        $proxyQuery = new ProxyQuery($this->createQueryBuilderStub());

        $filter->apply($proxyQuery, FilterData::fromArray(['value' => new \stdClass()]));
        static::assertTrue($filter->isActive());
    }

    public function testAssociationWithValidMapping(): void
    {
        $filter = new ModelFilter();
        $filter->initialize('field_name', [
            'mapping_type' => ClassMetadata::ONE_TO_ONE,
            'field_name' => 'field_name',
            'field_options' => ['class' => 'FooBar'],
            'association_mapping' => [
                'fieldName' => 'association_mapping',
            ],
        ]);

        $proxyQuery = new ProxyQuery($this->createQueryBuilderStub());

        $filter->apply($proxyQuery, FilterData::fromArray(['type' => EqualOperatorType::TYPE_EQUAL, 'value' => new \stdClass()]));

        self::assertSameQuery([
            'LEFT JOIN o.association_mapping AS s_association_mapping',
            'WHERE s_association_mapping.id = :field_name_0',
        ], $proxyQuery);
        static::assertTrue($filter->isActive());
    }

    public function testAssociationWithValidParentAssociationMappings(): void
    {
        $filter = new ModelFilter();
        $filter->initialize('field_name', [
            'mapping_type' => ClassMetadata::ONE_TO_ONE,
            'field_name' => 'field_name',
            'field_options' => ['class' => 'FooBar'],
            'parent_association_mappings' => [
                [
                    'fieldName' => 'association_mapping',
                ],
                [
                    'fieldName' => 'sub_association_mapping',
                ],
            ],
            'association_mapping' => [
                'fieldName' => 'sub_sub_association_mapping',
            ],
        ]);

        $proxyQuery = new ProxyQuery($this->createQueryBuilderStub());

        $filter->apply($proxyQuery, FilterData::fromArray(['type' => EqualOperatorType::TYPE_EQUAL, 'value' => new \stdClass()]));

        self::assertSameQuery([
            'LEFT JOIN o.association_mapping AS s_association_mapping',
            'LEFT JOIN s_association_mapping.sub_association_mapping AS s_association_mapping_sub_association_mapping',
            'LEFT JOIN s_association_mapping_sub_association_mapping.sub_sub_association_mapping AS s_association_mapping_sub_association_mapping_sub_sub_association_mapping',
            'WHERE s_association_mapping_sub_association_mapping_sub_sub_association_mapping.id = :field_name_0',
        ], $proxyQuery);
        static::assertTrue($filter->isActive());
    }
}
