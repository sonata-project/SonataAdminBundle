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

namespace Sonata\AdminBundle\Tests\Filter;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Filter\FilterFactory;
use Sonata\AdminBundle\Tests\Fixtures\Filter\FooFilter;
use Symfony\Component\Form\Extension\Core\Type\TextType;

final class FilterTest extends TestCase
{
    public function testFilter(): void
    {
        $filter = new FooFilter();

        static::assertSame(TextType::class, $filter->getFieldType());
        static::assertSame([], $filter->getFieldOptions());
        static::assertNull($filter->getLabel());
        static::assertNull($filter->getTranslationDomain());
        static::assertSame([], $filter->getLabelTranslationParameters());

        $options = [
            'label' => 'foo',
            'field_type' => 'integer',
            'field_options' => ['required' => true],
            'field_name' => 'name',
        ];

        $filter->setOptions($options);

        static::assertSame('foo', $filter->getOption('label'));
        static::assertSame('foo', $filter->getLabel());

        $expected = array_merge([
            'show_filter' => null,
            'advanced_filter' => true,
            'foo' => 'bar',
        ], $options);

        static::assertSame($expected, $filter->getOptions());
        static::assertSame('name', $filter->getFieldName());

        static::assertSame('default', $filter->getOption('fake', 'default'));

        $filter->setCondition('>');
        static::assertSame('>', $filter->getCondition());
    }

    public function testGetFieldOption(): void
    {
        $filter = new FooFilter();
        $filter->initialize('name', [
            'field_options' => ['foo' => 'bar', 'baz' => 12345],
        ]);

        static::assertSame(['foo' => 'bar', 'baz' => 12345], $filter->getFieldOptions());
        static::assertSame('bar', $filter->getFieldOption('foo'));
        static::assertSame(12345, $filter->getFieldOption('baz'));
    }

    public function testSetFieldOption(): void
    {
        $filter = new FooFilter();
        static::assertSame([], $filter->getFieldOptions());

        $filter->setFieldOption('foo', 'bar');
        $filter->setFieldOption('baz', 12345);

        static::assertSame(['foo' => 'bar', 'baz' => 12345], $filter->getFieldOptions());
        static::assertSame('bar', $filter->getFieldOption('foo'));
        static::assertSame(12345, $filter->getFieldOption('baz'));
    }

    public function testInitialize(): void
    {
        $filter = new FooFilter();
        $filter->initialize('name', [
            'field_name' => 'bar',
        ]);

        static::assertSame('name', $filter->getName());
        static::assertSame('bar', $filter->getOption('field_name'));
        static::assertSame('bar', $filter->getFieldName());
    }

    public function testLabel(): void
    {
        $filter = new FooFilter();
        $filter->setLabel('foo');

        static::assertSame('foo', $filter->getLabel());
    }

    public function testExceptionOnNonDefinedFilterName(): void
    {
        $filter = new FooFilter();

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(sprintf(
            'Seems like you didn\'t call `initialize()` on the filter `%s`. Did you create it through `%s::create()`?',
            FooFilter::class,
            FilterFactory::class
        ));

        $filter->getName();
    }

    public function testExceptionOnNonDefinedFieldName(): void
    {
        $filter = new FooFilter();
        $filter->initialize('foo');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The option `field_name` must be set for field: `foo`');

        $filter->getFieldName();
    }

    public function testIsActive(): void
    {
        $filter = new FooFilter();
        static::assertFalse($filter->isActive());

        $filter->callSetActive(true);
        static::assertTrue($filter->isActive());
    }

    public function testGetTranslationDomain(): void
    {
        $filter = new FooFilter();
        static::assertNull($filter->getTranslationDomain());
        $filter->setOption('translation_domain', 'baz');
        static::assertSame('baz', $filter->getTranslationDomain());
    }

    public function testGetFieldMappingException(): void
    {
        $filter = new FooFilter();
        $filter->initialize('foo');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The option `field_mapping` must be set for field: `foo`');

        $filter->getFieldMapping();
    }

    public function testGetFieldMapping(): void
    {
        $fieldMapping = [
            'fieldName' => 'username',
            'type' => 'string',
            'columnName' => 'username',
            'length' => 200,
            'unique' => true,
            'nullable' => false,
            'declared' => 'Foo\Bar\User',
        ];

        $filter = new FooFilter();
        $filter->setOption('field_mapping', $fieldMapping);
        static::assertSame($fieldMapping, $filter->getFieldMapping());
    }

    public function testGetParentAssociationMappings(): void
    {
        $parentAssociationMapping = [
            0 => ['fieldName' => 'user',
                'targetEntity' => 'Foo\Bar\User',
                'joinColumns' => [
                    0 => [
                        'name' => 'user_id',
                        'referencedColumnName' => 'user_id',
                    ],
                ],
                'type' => 2,
                'mappedBy' => null,
            ],
        ];

        $filter = new FooFilter();
        static::assertSame([], $filter->getParentAssociationMappings());
        $filter->setOption('parent_association_mappings', $parentAssociationMapping);
        static::assertSame($parentAssociationMapping, $filter->getParentAssociationMappings());
    }

    public function testGetAssociationMappingException(): void
    {
        $filter = new FooFilter();
        $filter->initialize('foo');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The option `association_mapping` must be set for field: `foo`');

        $filter->getAssociationMapping();
    }

    public function testGetAssociationMapping(): void
    {
        $associationMapping = [
            'fieldName' => 'user',
            'targetEntity' => 'Foo\Bar\User',
            'joinColumns' => [
                0 => [
                    'name' => 'user_id',
                    'referencedColumnName' => 'user_id',
                ],
            ],
            'type' => 2,
            'mappedBy' => null,
        ];

        $filter = new FooFilter();
        $filter->setOption('association_mapping', $associationMapping);
        static::assertSame($associationMapping, $filter->getAssociationMapping());
    }
}
