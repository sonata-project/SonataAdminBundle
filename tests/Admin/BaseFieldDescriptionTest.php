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

namespace Sonata\AdminBundle\Tests\Admin;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\BaseFieldDescription;
use Sonata\AdminBundle\Exception\NoValueException;
use Sonata\AdminBundle\Tests\Fixtures\Admin\FieldDescription;
use Sonata\AdminBundle\Tests\Fixtures\Entity\Foo;
use Sonata\AdminBundle\Tests\Fixtures\Entity\FooBoolean;
use Sonata\AdminBundle\Tests\Fixtures\Entity\FooCall;
use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;

class BaseFieldDescriptionTest extends TestCase
{
    use ExpectDeprecationTrait;

    public function testConstructingWithMapping(): void
    {
        $fieldMapping = ['field_name' => 'fieldName'];
        $associationMapping = ['association_model' => 'association_bar'];
        $parentAssociationMapping = ['parent_mapping' => 'parent_bar'];

        $description = new FieldDescription(
            'foo',
            ['foo' => 'bar'],
            $fieldMapping,
            $associationMapping,
            $parentAssociationMapping,
        );

        $this->assertSame($fieldMapping, $description->getFieldMapping());
        $this->assertSame($associationMapping, $description->getAssociationMapping());
        $this->assertSame($parentAssociationMapping, $description->getParentAssociationMappings());
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

        $this->assertCount(2, $description->getOptions());

        $this->assertSame('short_object_description_placeholder', $description->getOption('placeholder'));
        $description->setOptions(['placeholder' => false]);
        $this->assertFalse($description->getOption('placeholder'));

        $description->setOption('sortable', false);
        $this->assertFalse($description->isSortable());

        $description->setOption('sortable', 'field_name');
        $this->assertTrue($description->isSortable());
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testSetMappingType(): void
    {
        $description = new FieldDescription('name');

        $this->expectDeprecation('The "Sonata\AdminBundle\Admin\BaseFieldDescription::setMappingType()" method is deprecated since version 3.x and will be removed in 4.0.');

        $description->setMappingType('int');
        $this->assertSame('int', $description->getMappingType());
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testHelpOptions(): void
    {
        $description = new FieldDescription('name');

        $description->setHelp('Please enter an integer');
        $this->assertSame('Please enter an integer', $description->getHelp());

        $description->setOptions(['help' => 'fooHelp']);
        $this->assertSame('fooHelp', $description->getHelp());
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

    public function testGetValue(): void
    {
        $description = new FieldDescription('name');
        $description->setOption('code', 'getFoo');

        $mock = $this->getMockBuilder(\stdClass::class)
            ->setMethods(['getFoo'])
            ->getMock();
        $mock->expects($this->once())->method('getFoo')->willReturn(42);

        $this->assertSame(42, $description->getFieldValue($mock, 'fake'));

        /*
         * Test with One parameter int
         */
        $arg1 = 38;
        $oneParameter = [$arg1];
        $description1 = new FieldDescription('name');
        $description1->setOption('code', 'getWithOneParameter');
        $description1->setOption('parameters', $oneParameter);

        $mock1 = $this->getMockBuilder(\stdClass::class)
            ->setMethods(['getWithOneParameter'])
            ->getMock();
        $returnValue1 = $arg1 + 2;
        $mock1->expects($this->once())->method('getWithOneParameter')->with($this->equalTo($arg1))->willReturn($returnValue1);

        $this->assertSame(40, $description1->getFieldValue($mock1, 'fake'));

        /*
         * Test with Two parameters int
         */
        $arg2 = 4;
        $twoParameters = [$arg1, $arg2];
        $description2 = new FieldDescription('name');
        $description2->setOption('code', 'getWithTwoParameters');
        $description2->setOption('parameters', $twoParameters);

        $mock2 = $this->getMockBuilder(\stdClass::class)
            ->setMethods(['getWithTwoParameters'])
            ->getMock();
        $returnValue2 = $arg1 + $arg2;
        $mock2->method('getWithTwoParameters')->with($this->equalTo($arg1), $this->equalTo($arg2))->willReturn($returnValue2);
        $this->assertSame(42, $description2->getFieldValue($mock2, 'fake'));

        /*
         * Test with underscored attribute name
         */
        foreach (['getFake', 'isFake', 'hasFake'] as $method) {
            $description3 = new FieldDescription('name');
            $mock3 = $this->getMockBuilder(\stdClass::class)
                ->setMethods([$method])
                ->getMock();

            $mock3->expects($this->once())->method($method)->willReturn(42);
            $this->assertSame(42, $description3->getFieldValue($mock3, '_fake'));
        }

        $mock4 = $this->getMockBuilder('MockedTestObject')
            ->setMethods(['myMethod'])
            ->getMock();
        $mock4->expects($this->once())
            ->method('myMethod')
            ->willReturn('myMethodValue');

        $description4 = new FieldDescription('name');
        $description4->setOption('code', 'myMethod');

        $this->assertSame($description4->getFieldValue($mock4, null), 'myMethodValue');
    }

    public function testGetValueNoValueException(): void
    {
        $this->expectException(\Sonata\AdminBundle\Exception\NoValueException::class);

        $description = new FieldDescription('name');
        $mock = $this->getMockBuilder(\stdClass::class)
            ->setMethods(['getFoo'])
            ->getMock();

        $description->getFieldValue($mock, 'fake');
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testGetVirtualValue(): void
    {
        $description = new FieldDescription('name');
        $mock = $this->getMockBuilder(\stdClass::class)
            ->setMethods(['getFoo'])
            ->getMock();

        $description->setOption('virtual_field', true);
        $description->getFieldValue($mock, 'fake');
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

    /**
     * @group legacy
     */
    public function testCamelize(): void
    {
        $this->assertSame('FooBar', BaseFieldDescription::camelize('foo_bar'));
        $this->assertSame('FooBar', BaseFieldDescription::camelize('foo bar'));
        $this->assertSame('FOoBar', BaseFieldDescription::camelize('fOo bar'));
    }

    public function testGetInaccessibleValue(): void
    {
        $quux = 'quuX';
        $foo = new Foo();
        $foo->setQuux($quux);
        $ro = new \ReflectionObject($foo);
        $rm = $ro->getMethod('getQuux');
        $rm->setAccessible(true);
        $this->assertSame($quux, $rm->invokeArgs($foo, []));

        $description = new FieldDescription('name');

        $this->expectException(NoValueException::class);
        $description->getFieldValue($foo, 'quux');
    }

    public function testGetFieldValue(): void
    {
        $foo = new Foo();
        $foo->setBar('Bar');

        $description = new FieldDescription('name');
        $this->assertSame('Bar', $description->getFieldValue($foo, 'bar'));
        $foo->setBar('baR');
        $this->assertSame('baR', $description->getFieldValue($foo, 'bar'));

        $foo->qux = 'Qux';
        $this->assertSame('Qux', $description->getFieldValue($foo, 'qux'));
        $foo->qux = 'quX';
        $this->assertSame('quX', $description->getFieldValue($foo, 'qux'));

        $foo = new FooBoolean();
        $foo->setBar(true);
        $foo->setBaz(false);

        $description = new FieldDescription('name');
        $this->assertTrue($description->getFieldValue($foo, 'bar'));
        $this->assertFalse($description->getFieldValue($foo, 'baz'));

        $this->expectException(NoValueException::class);
        $description->getFieldValue($foo, 'inexistantMethod');
    }

    public function testGetFieldValueWithCodeOption(): void
    {
        $foo = new Foo();
        $foo->setBaz('Baz');

        $description = new FieldDescription('name');

        $description->setOption('code', 'getBaz');
        $this->assertSame('Baz', $description->getFieldValue($foo, 'inexistantMethod'));

        $description->setOption('code', 'inexistantMethod');
        $this->expectException(NoValueException::class);
        $description->getFieldValue($foo, 'inexistantMethod');
    }

    public function testGetFieldValueMagicCall(): void
    {
        $parameters = ['foo', 'bar'];
        $foo = new FooCall();

        $description = new FieldDescription('name');
        $description->setOption('parameters', $parameters);
        $this->assertSame(['inexistantMethod', $parameters], $description->getFieldValue($foo, 'inexistantMethod'));

        // repeating to cover retrieving cached getter
        $this->assertSame(['inexistantMethod', $parameters], $description->getFieldValue($foo, 'inexistantMethod'));
    }

    public function testGetFieldValueWithNullObject(): void
    {
        $foo = null;
        $description = new FieldDescription('name');
        $this->assertNull($description->getFieldValue($foo, 'bar'));
    }
}
