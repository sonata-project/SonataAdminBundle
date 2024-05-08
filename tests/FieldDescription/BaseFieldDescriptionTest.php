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

        static::assertSame('foo.bar', $description->getName());
        static::assertSame('foo.bar', $description->getFieldName());
    }

    public function testConstructingWithMapping(): void
    {
        $fieldMapping = ['fieldName' => 'fieldName'];
        $associationMapping = ['fieldName' => 'association_bar'];
        $parentAssociationMapping = [['fieldName' => 'parent_bar']];

        $description = new FieldDescription(
            'foo',
            ['foo' => 'bar'],
            $fieldMapping,
            $associationMapping,
            $parentAssociationMapping,
            'bar'
        );

        static::assertSame($fieldMapping, $description->getFieldMapping());
        static::assertSame($associationMapping, $description->getAssociationMapping());
        static::assertSame($parentAssociationMapping, $description->getParentAssociationMappings());
        static::assertSame('bar', $description->getFieldName());
    }

    public function testSetName(): void
    {
        $description = new FieldDescription('foo');
        static::assertSame('foo', $description->getFieldName());
        static::assertSame('foo', $description->getName());

        $description->setName('bar');
        static::assertSame('foo', $description->getFieldName());
        static::assertSame('bar', $description->getName());
    }

    public function testOptions(): void
    {
        $description = new FieldDescription('name');
        $description->setOption('foo', 'bar');

        static::assertNull($description->getOption('bar'));
        static::assertSame('bar', $description->getOption('foo'));

        $description->mergeOption('settings', ['value_1', 'value_2']);
        $description->mergeOption('settings', ['value_1', 'value_3']);

        static::assertSame(['value_1', 'value_2', 'value_1', 'value_3'], $description->getOption('settings'));

        $description->mergeOption('settings', ['value_4']);
        static::assertSame(['value_1', 'value_2', 'value_1', 'value_3', 'value_4'], $description->getOption('settings'));

        $description->mergeOption('bar', ['hello']);

        static::assertIsArray($description->getOption('bar'));
        static::assertCount(1, $description->getOption('bar'));

        $description->setOption('label', 'trucmuche');
        static::assertSame('trucmuche', $description->getLabel());
        static::assertNull($description->getTemplate());
        $description->setOptions(['type' => 'integer', 'template' => 'foo.twig.html']);

        static::assertSame('integer', $description->getType());
        static::assertSame('foo.twig.html', $description->getTemplate());

        static::assertCount(0, $description->getOptions());

        static::assertNull($description->getOption('placeholder'));
        $description->setOptions(['placeholder' => 'foo']);
        static::assertSame('foo', $description->getOption('placeholder'));

        $description->setOption('sortable', false);
        static::assertFalse($description->isSortable());

        $description->setOption('sortable', 'field_name');
        static::assertTrue($description->isSortable());
    }

    public function testAdmin(): void
    {
        $description = new FieldDescription('name');

        $admin = $this->createMock(AdminInterface::class);
        $description->setAdmin($admin);
        static::assertInstanceOf(AdminInterface::class, $description->getAdmin());

        $associationAdmin = $this->createMock(AdminInterface::class);
        $associationAdmin->expects(static::once())->method('setParentFieldDescription');

        static::assertFalse($description->hasAssociationAdmin());
        $description->setAssociationAdmin($associationAdmin);
        static::assertTrue($description->hasAssociationAdmin());
        static::assertInstanceOf(AdminInterface::class, $description->getAssociationAdmin());

        $parent = $this->createMock(AdminInterface::class);
        $description->setParent($parent);
        static::assertInstanceOf(AdminInterface::class, $description->getParent());
    }

    public function testGetFieldValueNoValueException(): void
    {
        $admin = $this->createStub(AdminInterface::class);
        $description = new FieldDescription('name');
        $description->setAdmin($admin);
        $mock = $this->getMockBuilder(\stdClass::class)->addMethods(['getFoo'])->getMock();

        $this->expectException(NoValueException::class);
        $this->callMethod($description, 'getFieldValue', [$mock, 'fake']);
    }

    public function testGetVirtualFieldValue(): void
    {
        $description = new FieldDescription('name');
        $mock = $this->getMockBuilder(\stdClass::class)->addMethods(['getFoo'])->getMock();

        $description->setOption('virtual_field', true);
        static::assertNull($this->callMethod($description, 'getFieldValue', [$mock, 'fake']));
    }

    public function testGetFieldValueWithNullObject(): void
    {
        $description = new FieldDescription('name');
        static::assertNull($this->callMethod($description, 'getFieldValue', [null, 'fake']));
    }

    public function testGetFieldValueWithAccessor(): void
    {
        $description = new FieldDescription('name', ['accessor' => 'foo']);
        $mock = $this->getMockBuilder(\stdClass::class)->addMethods(['getFoo'])->getMock();
        $mock->expects(static::once())->method('getFoo')->willReturn(42);
        static::assertSame(42, $this->callMethod($description, 'getFieldValue', [$mock, 'fake']));
    }

    public function testGetFieldValueWithTopLevelFunctionName(): void
    {
        $description = new FieldDescription('microtime');
        $mock = $this->getMockBuilder(\stdClass::class)->addMethods(['getMicrotime'])->getMock();
        $mock->expects(static::once())->method('getMicrotime')->willReturn(42);
        static::assertSame(42, $this->callMethod($description, 'getFieldValue', [$mock, 'microtime']));
    }

    public function testGetFieldValueWithCallableAccessor(): void
    {
        $description = new FieldDescription('name', [
            // @phpstan-ignore-next-line
            'accessor' => static fn (object $object): int => $object->getFoo(),
        ]);
        $mock = $this->getMockBuilder(\stdClass::class)->addMethods(['getFoo'])->getMock();
        $mock->expects(static::once())->method('getFoo')->willReturn(42);
        static::assertSame(42, $this->callMethod($description, 'getFieldValue', [$mock, 'fake']));
    }

    public function testGetFieldValueWithMagicCall(): void
    {
        $foo = new FooCall();

        $description = new FieldDescription('name');
        static::assertSame(['getFake', []], $this->callMethod($description, 'getFieldValue', [$foo, 'fake']));

        // repeating to cover retrieving cached getter
        static::assertSame(['getFake', []], $this->callMethod($description, 'getFieldValue', [$foo, 'fake']));
    }

    /**
     * @dataProvider provideGetFieldValueWithMethodCases
     */
    public function testGetFieldValueWithMethod(string $method): void
    {
        $description = new FieldDescription('name');
        $mock = $this->getMockBuilder(\stdClass::class)->addMethods([$method])->getMock();

        $mock->method($method)->willReturn(42);
        static::assertSame(42, $this->callMethod($description, 'getFieldValue', [$mock, 'fake_field_value']));
        static::assertSame(42, $this->callMethod($description, 'getFieldValue', [$mock, 'fakeFieldValue']));
    }

    /**
     * @phpstan-return iterable<array-key, array{string}>
     */
    public function provideGetFieldValueWithMethodCases(): iterable
    {
        yield ['getFakeFieldValue'];
        yield ['isFakeFieldValue'];
        yield ['hasFakeFieldValue'];
    }

    public function testGetFieldValueWithChainedFieldName(): void
    {
        $mockChild = $this->getMockBuilder(\stdClass::class)->addMethods(['getFoo'])->getMock();
        $mockChild->expects(static::once())->method('getFoo')->willReturn(42);

        $mockParent = $this->getMockBuilder(\stdClass::class)->addMethods(['getChild'])->getMock();
        $mockParent->expects(static::once())->method('getChild')->willReturn($mockChild);

        $description = new FieldDescription('name');
        static::assertSame(42, $this->callMethod($description, 'getFieldValue', [$mockParent, 'child.foo']));
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

        $admin->expects(static::once())
            ->method('getTranslationDomain')
            ->willReturn('AdminDomain');

        static::assertSame('AdminDomain', $description->getTranslationDomain());

        $admin->expects(static::never())
            ->method('getTranslationDomain');
        $description->setOption('translation_domain', 'ExtensionDomain');
        static::assertSame('ExtensionDomain', $description->getTranslationDomain());
    }

    public function testGetTranslationDomainWithFalse(): void
    {
        $description = new FieldDescription('name', ['translation_domain' => false]);

        $admin = $this->createMock(AdminInterface::class);
        $description->setAdmin($admin);

        $admin->expects(static::never())
            ->method('getTranslationDomain');

        static::assertFalse($description->getTranslationDomain());
    }

    /**
     * @param mixed[] $args
     */
    protected function callMethod(object $obj, string $name, array $args = []): mixed
    {
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method->invokeArgs($obj, $args);
    }
}
