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

namespace Sonata\AdminBundle\Tests\Builder;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Builder\AbstractFormContractor;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Form\Type\AdminType;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Form\Type\ModelHiddenType;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\Form\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;

final class AbstractFormContractorTest extends TestCase
{
    /**
     * @var FormFactoryInterface&MockObject
     */
    private $formFactory;

    /**
     * @var \Sonata\DoctrineMongoDBAdminBundle\Builder\FormContractor
     */
    private $formContractor;

    /**
     * @var MockObject&FieldDescriptionInterface
     */
    private $fieldDescription;

    protected function setUp(): void
    {
        parent::setUp();

        // NEXT_MAJOR: Mock `FieldDescriptionInterface` instead and replace `getTargetEntity()` with `getTargetModel().
        $this->fieldDescription = $this->getMockBuilder(FieldDescriptionInterface::class)
            ->addMethods(['getTargetModel', 'describesAssociation', 'describesSingleValuedAssociation'])
            ->getMockForAbstractClass();

        $this->formFactory = $this->createMock(FormFactoryInterface::class);

        $this->formContractor = new class($this->formFactory) extends AbstractFormContractor {
            protected function hasAssociation(FieldDescriptionInterface $fieldDescription): bool
            {
                return $fieldDescription->describesAssociation();
            }

            protected function hasSingleValueAssociation(FieldDescriptionInterface $fieldDescription): bool
            {
                return $fieldDescription->describesSingleValuedAssociation();
            }
        };
    }

    public function testGetFormBuilder(): void
    {
        $this->formFactory->expects($this->once())->method('createNamedBuilder')
            ->willReturn($this->createMock(FormBuilderInterface::class));

        $this->assertInstanceOf(
            FormBuilderInterface::class,
            $this->formContractor->getFormBuilder('test', ['foo' => 'bar'])
        );
    }

    public function testDefaultOptionsForSonataFormTypes(): void
    {
        $admin = $this->createMock(AdminInterface::class);
        $modelClass = 'FooModel';

        $modelManager = $this->createStub(ModelManagerInterface::class);
        $admin->method('getModelManager')->willReturn($modelManager);
        $admin->method('getClass')->willReturn($modelClass);

        $this->fieldDescription->method('getAdmin')->willReturn($admin);
        $this->fieldDescription->method('getTargetModel')->willReturn($modelClass);
        $this->fieldDescription->method('getAssociationAdmin')->willReturn($admin);

        $modelTypes = [
            ModelType::class,
            ModelListType::class,
            ModelHiddenType::class,
            ModelAutocompleteType::class,
        ];
        $adminTypes = [
            AdminType::class,
        ];
        $collectionTypes = [
            CollectionType::class,
        ];

        // model types
        foreach ($modelTypes as $formType) {
            $options = $this->formContractor->getDefaultOptions($formType, $this->fieldDescription);
            $this->assertSame($this->fieldDescription, $options['sonata_field_description']);
            $this->assertSame($modelClass, $options['class']);
            $this->assertSame($modelManager, $options['model_manager']);
        }

        // admin type
        $this->fieldDescription
            ->method('describesSingleValuedAssociation')
            ->willReturn(true);
        foreach ($adminTypes as $formType) {
            $options = $this->formContractor->getDefaultOptions($formType, $this->fieldDescription);
            $this->assertSame($this->fieldDescription, $options['sonata_field_description']);
            $this->assertSame($modelClass, $options['data_class']);
            $this->assertFalse($options['btn_add']);
            $this->assertFalse($options['delete']);
        }

        // collection type
        foreach ($collectionTypes as $index => $formType) {
            $options = $this->formContractor->getDefaultOptions($formType, $this->fieldDescription, [
                'by_reference' => false,
            ]);
            $this->assertSame($this->fieldDescription, $options['sonata_field_description']);
            $this->assertSame(AdminType::class, $options['type']);
            $this->assertTrue($options['modifiable']);
            $this->assertSame($this->fieldDescription, $options['type_options']['sonata_field_description']);
            $this->assertSame($modelClass, $options['type_options']['data_class']);
            $this->assertFalse($options['type_options']['collection_by_reference']);
        }
    }

    public function testAdminClassAttachForFieldDescriptionWithAssociation(): void
    {
        $admin = $this->createMock(AdminInterface::class);

        $this->fieldDescription
            ->method('describesAssociation')
            ->willReturn(true);

        // Then
        $admin
            ->expects($this->once())
            ->method('attachAdminClass')
            ->with($this->fieldDescription)
        ;

        // When
        $this->formContractor->fixFieldDescription($admin, $this->fieldDescription);
    }

    public function testAdminClassAttachForNotMappedField(): void
    {
        $admin = $this->createMock(AdminInterface::class);

        $this->fieldDescription
            ->method('describesAssociation')
            ->willReturn(false);
        $this->fieldDescription->method('getOption')->with($this->logicalOr(
            $this->equalTo('edit'),
            $this->equalTo('admin_code')
        ))->willReturn('sonata.admin.code');

        // Then
        $admin
            ->expects($this->once())
            ->method('attachAdminClass')
            ->with($this->fieldDescription)
        ;

        // When
        $this->formContractor->fixFieldDescription($admin, $this->fieldDescription);
    }

    /**
     * @dataProvider getFieldDescriptionValidationProvider
     */
    public function testThrowsExceptionWithInvalidFieldDescriptionInGetDefaultOptions(string $formType): void
    {
        $admin = $this->createStub(AdminInterface::class);
        $admin->method('getClass')->willReturn('Foo');

        $this->fieldDescription->method('getAdmin')->willReturn($admin);
        $this->fieldDescription->method('getAssociationAdmin')->willReturn(null);
        $this->fieldDescription->method('describesSingleValuedAssociation')->willReturn(false);

        $this->expectException(\RuntimeException::class);
        $this->formContractor->getDefaultOptions($formType, $this->fieldDescription);
    }

    /**
     * @return iterable<array{0: class-string}>
     */
    public function getFieldDescriptionValidationProvider(): iterable
    {
        yield 'ModelAutocompleteType, no target model' => [
            ModelAutocompleteType::class,
        ];

        yield 'AdminType, no association admin' => [
            AdminType::class,
        ];

        yield 'AdminType, no single valued association' => [
            AdminType::class,
        ];

        yield 'CollectionType, no association admin' => [
            CollectionType::class,
        ];
    }
}
