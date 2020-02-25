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
use Sonata\AdminBundle\Tests\Fixtures\Filter\FooFilter;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class FilterTest extends TestCase
{
    public function testFilter(): void
    {
        $filter = new FooFilter();

        $this->assertSame(TextType::class, $filter->getFieldType());
        $this->assertSame(['required' => false], $filter->getFieldOptions());
        $this->assertNull($filter->getLabel());

        $options = [
            'label' => 'foo',
            'field_type' => 'integer',
            'field_options' => ['required' => true],
            'field_name' => 'name',
        ];

        $filter->setOptions($options);

        $this->assertSame('foo', $filter->getOption('label'));
        $this->assertSame('foo', $filter->getLabel());

        $expected = array_merge([
            'show_filter' => null,
            'advanced_filter' => true,
            'foo' => 'bar',
        ], $options);

        $this->assertSame($expected, $filter->getOptions());
        $this->assertSame('name', $filter->getFieldName());

        $this->assertSame('default', $filter->getOption('fake', 'default'));

        $filter->setValue(42);
        $this->assertSame(42, $filter->getValue());

        $filter->setCondition('>');
        $this->assertSame('>', $filter->getCondition());
    }

    public function testGetFieldOption(): void
    {
        $filter = new FooFilter();
        $filter->initialize('name', [
            'field_options' => ['foo' => 'bar', 'baz' => 12345],
        ]);

        $this->assertSame(['foo' => 'bar', 'baz' => 12345], $filter->getFieldOptions());
        $this->assertSame('bar', $filter->getFieldOption('foo'));
        $this->assertSame(12345, $filter->getFieldOption('baz'));
    }

    public function testSetFieldOption(): void
    {
        $filter = new FooFilter();
        $this->assertSame(['required' => false], $filter->getFieldOptions());

        $filter->setFieldOption('foo', 'bar');
        $filter->setFieldOption('baz', 12345);

        $this->assertSame(['foo' => 'bar', 'baz' => 12345], $filter->getFieldOptions());
        $this->assertSame('bar', $filter->getFieldOption('foo'));
        $this->assertSame(12345, $filter->getFieldOption('baz'));
    }

    public function testInitialize(): void
    {
        $filter = new FooFilter();
        $filter->initialize('name', [
            'field_name' => 'bar',
        ]);

        $this->assertSame('name', $filter->getName());
        $this->assertSame('bar', $filter->getOption('field_name'));
        $this->assertSame('bar', $filter->getFieldName());
    }

    public function testLabel(): void
    {
        $filter = new FooFilter();
        $filter->setLabel('foo');

        $this->assertSame('foo', $filter->getLabel());
    }

    public function testExceptionOnNonDefinedFieldName(): void
    {
        $this->expectException(\RuntimeException::class);

        $filter = new FooFilter();

        $filter->getFieldName();
    }

    /**
     * @dataProvider isActiveData
     *
     * @param $expected
     * @param $value
     */
    public function testIsActive(bool $expected, array $value): void
    {
        $filter = new FooFilter();
        $filter->setValue($value);

        $this->assertSame($expected, $filter->isActive());
    }

    public function isActiveData(): array
    {
        return [
            [false, []],
            [false, ['value' => null]],
            [false, ['value' => '']],
            [false, ['value' => false]],
            [true, ['value' => 'active']],
        ];
    }

    public function testGetTranslationDomain(): void
    {
        $filter = new FooFilter();
        $this->assertNull($filter->getTranslationDomain());
        $filter->setOption('translation_domain', 'baz');
        $this->assertSame('baz', $filter->getTranslationDomain());
    }

    public function testGetFieldMappingException(): void
    {
        $filter = new FooFilter();
        $filter->initialize('foo');

        try {
            $filter->getFieldMapping();
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString(
                'The option `field_mapping` must be set for field: `foo`',
                $e->getMessage()
            );

            return;
        }

        $this->fail('Failed asserting that exception of type "\RuntimeException" is thrown.');
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
        $this->assertSame($fieldMapping, $filter->getFieldMapping());
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
        $this->assertSame([], $filter->getParentAssociationMappings());
        $filter->setOption('parent_association_mappings', $parentAssociationMapping);
        $this->assertSame($parentAssociationMapping, $filter->getParentAssociationMappings());
    }

    public function testGetAssociationMappingException(): void
    {
        $filter = new FooFilter();
        $filter->initialize('foo');

        try {
            $filter->getAssociationMapping();
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString(
                'The option `association_mapping` must be set for field: `foo`',
                $e->getMessage()
            );

            return;
        }

        $this->fail('Failed asserting that exception of type "\RuntimeException" is thrown.');
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
        $this->assertSame($associationMapping, $filter->getAssociationMapping());
    }
}
