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

final class BaseFieldDescriptionTest extends TestCase
{
    public function testConstruct(): void
    {
        $description = new FieldDescription('foo.bar');

        self::assertSame('foo.bar', $description->getName());
        self::assertSame('foo.bar', $description->getFieldName());
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

        self::assertSame($fieldMapping, $description->getFieldMapping());
        self::assertSame($associationMapping, $description->getAssociationMapping());
        self::assertSame($parentAssociationMapping, $description->getParentAssociationMappings());
        self::assertSame('bar', $description->getFieldName());
    }

    public function testSetName(): void
    {
        $description = new FieldDescription('foo');
        self::assertSame('foo', $description->getFieldName());
        self::assertSame('foo', $description->getName());

        $description->setName('bar');
        self::assertSame('foo', $description->getFieldName());
        self::assertSame('bar', $description->getName());
    }

    public function testOptions(): void
    {
        $description = new FieldDescription('name');
        $description->setOption('foo', 'bar');

        self::assertNull($description->getOption('bar'));
        self::assertSame('bar', $description->getOption('foo'));

        $description->mergeOption('settings', ['value_1', 'value_2']);
        $description->mergeOption('settings', ['value_1', 'value_3']);

        self::assertSame(['value_1', 'value_2', 'value_1', 'value_3'], $description->getOption('settings'));

        $description->mergeOption('settings', ['value_4']);
        self::assertSame(['value_1', 'value_2', 'value_1', 'value_3', 'value_4'], $description->getOption('settings'));

        $description->mergeOption('bar', ['hello']);

        self::assertCount(1, $description->getOption('bar'));

        $description->setOption('label', 'trucmuche');
        self::assertSame('trucmuche', $description->getLabel());
        self::assertNull($description->getTemplate());
        $description->setOptions(['type' => 'integer', 'template' => 'foo.twig.html']);

        self::assertSame('integer', $description->getType());
        self::assertSame('foo.twig.html', $description->getTemplate());

        self::assertCount(0, $description->getOptions());

        self::assertNull($description->getOption('placeholder'));
        $description->setOptions(['placeholder' => 'foo']);
        self::assertSame('foo', $description->getOption('placeholder'));

        $description->setOption('sortable', false);
        self::assertFalse($description->isSortable());

        $description->setOption('sortable', 'field_name');
        self::assertTrue($description->isSortable());
    }

    public function testAdmin(): void
    {
        $description = new FieldDescription('name');

        $admin = $this->createMock(AdminInterface::class);
        $description->setAdmin($admin);
        self::assertInstanceOf(AdminInterface::class, $description->getAdmin());

        $associationAdmin = $this->createMock(AdminInterface::class);
        $associationAdmin->expects(self::once())->method('setParentFieldDescription');

        self::assertFalse($description->hasAssociationAdmin());
        $description->setAssociationAdmin($associationAdmin);
        self::assertTrue($description->hasAssociationAdmin());
        self::assertInstanceOf(AdminInterface::class, $description->getAssociationAdmin());

        $parent = $this->createMock(AdminInterface::class);
        $description->setParent($parent);
        self::assertInstanceOf(AdminInterface::class, $description->getParent());
    }

    public function testGetFieldValueNoValueException(): void
    {
        $this->expectException(NoValueException::class);

        $description = new FieldDescription('name');
        $mock = $this->getMockBuilder(\stdClass::class)->addMethods(['getFoo'])->getMock();

        $this->callMethod($description, 'getFieldValue', [$mock, 'fake']);
    }

    public function testGetVirtualFieldValue(): void
    {
        $description = new FieldDescription('name');
        $mock = $this->getMockBuilder(\stdClass::class)->addMethods(['getFoo'])->getMock();

        $description->setOption('virtual_field', true);
        self::assertNull($this->callMethod($description, 'getFieldValue', [$mock, 'fake']));
    }

    public function testGetFieldValueWithNullObject(): void
    {
        $description = new FieldDescription('name');
        self::assertNull($this->callMethod($description, 'getFieldValue', [null, 'fake']));
    }

    public function testGetFieldValueWithAccessor(): void
    {
        $description = new FieldDescription('name', ['accessor' => 'foo']);
        $mock = $this->getMockBuilder(\stdClass::class)->addMethods(['getFoo'])->getMock();
        $mock->expects(self::once())->method('getFoo')->willReturn(42);
        self::assertSame(42, $this->callMethod($description, 'getFieldValue', [$mock, 'fake']));
    }

    public function testGetFieldValueWithTopLevelFunctionName(): void
    {
        $description = new FieldDescription('microtime');
        $mock = $this->getMockBuilder(\stdClass::class)->addMethods(['getMicrotime'])->getMock();
        $mock->expects(self::once())->method('getMicrotime')->willReturn(42);
        self::assertSame(42, $this->callMethod($description, 'getFieldValue', [$mock, 'microtime']));
    }

    public function testGetFieldValueWithCallableAccessor(): void
    {
        $description = new FieldDescription('name', [
            'accessor' => static function (object $object): int {
                // @phpstan-ignore-next-line
                return $object->getFoo();
            },
        ]);
        $mock = $this->getMockBuilder(\stdClass::class)->addMethods(['getFoo'])->getMock();
        $mock->expects(self::once())->method('getFoo')->willReturn(42);
        self::assertSame(42, $this->callMethod($description, 'getFieldValue', [$mock, 'fake']));
    }

    public function testGetFieldValueWithMagicCall(): void
    {
        $foo = new FooCall();

        $description = new FieldDescription('name');
        self::assertSame(['getFake', []], $this->callMethod($description, 'getFieldValue', [$foo, 'fake']));

        // repeating to cover retrieving cached getter
        self::assertSame(['getFake', []], $this->callMethod($description, 'getFieldValue', [$foo, 'fake']));
    }

    /**
     * @dataProvider getFieldValueWithFieldNameDataProvider
     */
    public function testGetFieldValueWithMethod(string $method): void
    {
        $description = new FieldDescription('name');
        $mock = $this->getMockBuilder(\stdClass::class)->addMethods([$method])->getMock();

        $mock->method($method)->willReturn(42);
        self::assertSame(42, $this->callMethod($description, 'getFieldValue', [$mock, 'fake_field_value']));
        self::assertSame(42, $this->callMethod($description, 'getFieldValue', [$mock, 'fakeFieldValue']));
    }

    /**
     * @phpstan-return iterable<array-key, array{string}>
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
        $mockChild->expects(self::once())->method('getFoo')->willReturn(42);

        $mockParent = $this->getMockBuilder(\stdClass::class)->addMethods(['getChild'])->getMock();
        $mockParent->expects(self::once())->method('getChild')->willReturn($mockChild);

        $description = new FieldDescription('name');
        self::assertSame(42, $this->callMethod($description, 'getFieldValue', [$mockParent, 'child.foo']));
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

        $admin->expects(self::once())
            ->method('getTranslationDomain')
            ->willReturn('AdminDomain');

        self::assertSame('AdminDomain', $description->getTranslationDomain());

        $admin->expects(self::never())
            ->method('getTranslationDomain');
        $description->setOption('translation_domain', 'ExtensionDomain');
        self::assertSame('ExtensionDomain', $description->getTranslationDomain());
    }

    public function testGetTranslationDomainWithFalse(): void
    {
        $description = new FieldDescription('name', ['translation_domain' => false]);

        $admin = $this->createMock(AdminInterface::class);
        $description->setAdmin($admin);

        $admin->expects(self::never())
            ->method('getTranslationDomain');

        self::assertFalse($description->getTranslationDomain());
    }

    /**
     * @param mixed[] $args
     *
     * @return mixed
     */
    protected function callMethod(object $obj, string $name, array $args = [])
    {
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method->invokeArgs($obj, $args);
    }
}
