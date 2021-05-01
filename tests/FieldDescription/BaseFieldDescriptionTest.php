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

namespace Sonata\AdminBundle\Tests\FieldDescription;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Exception\NoValueException;
use Sonata\AdminBundle\Tests\Fixtures\Entity\FooCall;
use Sonata\AdminBundle\Tests\Fixtures\FieldDescription\FieldDescription;

class BaseFieldDescriptionTest extends TestCase
{
    public function testConstruct(): void
    {
        $description = new FieldDescription('foo.bar');

        $this->assertSame('foo.bar', $description->getName());
        $this->assertSame('foo.bar', $description->getFieldName());
    }

    public function testConstructingWithMapping(): void
    {
        $fieldMapping = ['field_name' => 'fieldName'];
        $associationMapping = ['association_model' => 'association_bar'];
        $parentAssociationMapping = [['parent_mapping' => 'parent_bar']];

        $description = new FieldDescription(
            'foo',
            ['foo' => 'bar'],
            $fieldMapping,
            $associationMapping,
            $parentAssociationMapping,
            'bar'
        );

        $this->assertSame($fieldMapping, $description->getFieldMapping());
        $this->assertSame($associationMapping, $description->getAssociationMapping());
        $this->assertSame($parentAssociationMapping, $description->getParentAssociationMappings());
        $this->assertSame('bar', $description->getFieldName());
    }

    public function testSetName(): void
    {
        $description = new FieldDescription('foo');
        $this->assertSame('foo', $description->getFieldName());
        $this->assertSame('foo', $description->getName());

        $description->setName('bar');
        $this->assertSame('foo', $description->getFieldName());
        $this->assertSame('bar', $description->getName());
    }

    public function testOptions(): void
    {
        $description = new FieldDescription('name');
        $description->setOption('foo', 'bar');

        $this->assertNull($description->getOption('bar'));
        $this->assertSame('bar', $description->getOption('foo'));

        $description->mergeOptions(['settings' => ['value_1', 'value_2']]);
        $description->mergeOptions(['settings' => ['value_1', 'value_3']]);

        $this->assertSame(['value_1', 'value_2', 'value_1', 'value_3'], $description->getOption('settings'));

        $description->mergeOption('settings', ['value_4']);
        $this->assertSame(['value_1', 'value_2', 'value_1', 'value_3', 'value_4'], $description->getOption('settings'));

        $description->mergeOption('bar', ['hello']);

        $this->assertCount(1, $description->getOption('bar'));

        $description->setOption('label', 'trucmuche');
        $this->assertSame('trucmuche', $description->getLabel());
        $this->assertNull($description->getTemplate());
        $description->setOptions(['type' => 'integer', 'template' => 'foo.twig.html']);

        $this->assertSame('integer', $description->getType());
        $this->assertSame('foo.twig.html', $description->getTemplate());

        $this->assertCount(0, $description->getOptions());

        $this->assertNull($description->getOption('placeholder'));
        $description->setOptions(['placeholder' => 'foo']);
        $this->assertSame('foo', $description->getOption('placeholder'));

        $description->setOption('sortable', false);
        $this->assertFalse($description->isSortable());

        $description->setOption('sortable', 'field_name');
        $this->assertTrue($description->isSortable());
    }

    public function testAdmin(): void
    {
        $description = new FieldDescription('name');

        $admin = $this->getMockForAbstractClass(AdminInterface::class);
        $description->setAdmin($admin);
        $this->assertInstanceOf(AdminInterface::class, $description->getAdmin());

        $associationAdmin = $this->getMockForAbstractClass(AdminInterface::class);
        $associationAdmin->expects($this->once())->method('setParentFieldDescription');

        $this->assertFalse($description->hasAssociationAdmin());
        $description->setAssociationAdmin($associationAdmin);
        $this->assertTrue($description->hasAssociationAdmin());
        $this->assertInstanceOf(AdminInterface::class, $description->getAssociationAdmin());

        $parent = $this->getMockForAbstractClass(AdminInterface::class);
        $description->setParent($parent);
        $this->assertInstanceOf(AdminInterface::class, $description->getParent());
    }

    public function testGetFieldValueNoValueException(): void
    {
        $this->expectException(NoValueException::class);

        $description = new FieldDescription('name');
        $mock = $this->getMockBuilder(\stdClass::class)->addMethods(['getFoo'])->getMock();

        $description->getFieldValue($mock, 'fake');
    }

    public function testGetVirtualFieldValue(): void
    {
        $description = new FieldDescription('name');
        $mock = $this->getMockBuilder(\stdClass::class)->addMethods(['getFoo'])->getMock();

        $description->setOption('virtual_field', true);
        $this->assertNull($description->getFieldValue($mock, 'fake'));
    }

    public function testGetFieldValueWithNullObject(): void
    {
        $foo = null;
        $description = new FieldDescription('name');
        $this->assertNull($description->getFieldValue(null, 'fake'));
    }

    public function testGetFieldValueWithAccessor(): void
    {
        $description = new FieldDescription('name', ['accessor' => 'foo']);
        $mock = $this->getMockBuilder(\stdClass::class)->addMethods(['getFoo'])->getMock();
        $mock->expects($this->once())->method('getFoo')->willReturn(42);
        $this->assertSame(42, $description->getFieldValue($mock, 'fake'));
    }

    public function testGetFieldValueWithTopLevelFunctionName(): void
    {
        $description = new FieldDescription('microtime');
        $mock = $this->getMockBuilder(\stdClass::class)->addMethods(['getMicrotime'])->getMock();
        $mock->expects($this->once())->method('getMicrotime')->willReturn(42);
        $this->assertSame(42, $description->getFieldValue($mock, 'microtime'));
    }

    public function testGetFieldValueWithCallableAccessor(): void
    {
        $description = new FieldDescription('name', [
            'accessor' => static function (object $object): int {
                return $object->getFoo();
            },
        ]);
        $mock = $this->getMockBuilder(\stdClass::class)->addMethods(['getFoo'])->getMock();
        $mock->expects($this->once())->method('getFoo')->willReturn(42);
        $this->assertSame(42, $description->getFieldValue($mock, 'fake'));
    }

    public function testGetFieldValueWithMagicCall(): void
    {
        $foo = new FooCall();

        $description = new FieldDescription('name');
        $this->assertSame(['getFake', []], $description->getFieldValue($foo, 'fake'));

        // repeating to cover retrieving cached getter
        $this->assertSame(['getFake', []], $description->getFieldValue($foo, 'fake'));
    }

    /**
     * @dataProvider getFieldValueWithFieldNameDataProvider
     */
    public function testGetFieldValueWithMethod(string $method): void
    {
        $description = new FieldDescription('name');
        $mock = $this->getMockBuilder(\stdClass::class)->addMethods([$method])->getMock();

        $mock->method($method)->willReturn(42);
        $this->assertSame(42, $description->getFieldValue($mock, 'fake_field_value'));
        $this->assertSame(42, $description->getFieldValue($mock, 'fakeFieldValue'));
    }

    /**
     * @phpstan-return iterable<array{string}>
     */
    public function getFieldValueWithFieldNameDataProvider(): iterable
    {
        return [
            ['getFakeFieldValue'],
            ['isFakeFieldValue'],
            ['hasFakeFieldValue'],
        ];
    }

    public function testGetFieldValueWithChainedFieldName(): void
    {
        $mockChild = $this->getMockBuilder(\stdClass::class)->addMethods(['getFoo'])->getMock();
        $mockChild->expects($this->once())->method('getFoo')->willReturn(42);

        $mockParent = $this->getMockBuilder(\stdClass::class)->addMethods(['getChild'])->getMock();
        $mockParent->expects($this->once())->method('getChild')->willReturn($mockChild);

        $description4 = new FieldDescription('name');
        $this->assertSame(42, $description4->getFieldValue($mockParent, 'child.foo'));
    }

    public function testExceptionOnNonArrayOption(): void
    {
        $this->expectException(\RuntimeException::class);

        $description = new FieldDescription('name');
        $description->setOption('bar', 'hello');
        $description->mergeOption('bar', ['exception']);
    }

    public function testGetTranslationDomain(): void
    {
        $description = new FieldDescription('name');

        $admin = $this->createMock(AdminInterface::class);
        $description->setAdmin($admin);

        $admin->expects($this->once())
            ->method('getTranslationDomain')
            ->willReturn('AdminDomain');

        $this->assertSame('AdminDomain', $description->getTranslationDomain());

        $admin->expects($this->never())
            ->method('getTranslationDomain');
        $description->setOption('translation_domain', 'ExtensionDomain');
        $this->assertSame('ExtensionDomain', $description->getTranslationDomain());
    }
}
