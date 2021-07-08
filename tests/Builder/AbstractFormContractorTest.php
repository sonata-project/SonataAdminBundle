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
use Sonata\AdminBundle\Builder\FormContractorInterface;
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
use Symfony\Component\Form\FormRegistryInterface;
use Symfony\Component\Form\ResolvedFormTypeInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;

final class AbstractFormContractorTest extends TestCase
{
    /**
     * @var FormFactoryInterface&MockObject
     */
    private $formFactory;

    /**
     * @var FormContractorInterface
     */
    private $formContractor;

    /**
     * @var MockObject&FieldDescriptionInterface
     */
    private $fieldDescription;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fieldDescription = $this->createMock(FieldDescriptionInterface::class);

        $this->formFactory = $this->createMock(FormFactoryInterface::class);
        $formRegistry = $this->createStub(FormRegistryInterface::class);
        $formRegistry->method('getType')->willReturnCallback(function (string $type) {
            $resolvedType = $this->createStub(ResolvedFormTypeInterface::class);
            if ('MyCustomType' === $type) {
                $parentType = $this->createStub(ResolvedFormTypeInterface::class);
                $parentType->method('getInnerType')->willReturn(new ModelType($this->createStub(PropertyAccessor::class)));
                $resolvedType->method('getParent')->willReturn($parentType);
            }

            return $resolvedType;
        });

        $this->formContractor = new class($this->formFactory, $formRegistry) extends AbstractFormContractor {
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
        $this->formFactory->expects(self::once())->method('createNamedBuilder')
            ->willReturn($this->createMock(FormBuilderInterface::class));

        self::assertInstanceOf(
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
        $this->fieldDescription->method('hasAssociationAdmin')->willReturn(true);

        $modelTypes = [
            ModelType::class,
            ModelListType::class,
            ModelHiddenType::class,
            ModelAutocompleteType::class,
            'MyCustomType',
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
            self::assertSame($this->fieldDescription, $options['sonata_field_description']);
            self::assertSame($modelClass, $options['class']);
            self::assertSame($modelManager, $options['model_manager']);
        }

        // admin type
        $this->fieldDescription
            ->method('describesSingleValuedAssociation')
            ->willReturn(true);
        foreach ($adminTypes as $formType) {
            $options = $this->formContractor->getDefaultOptions($formType, $this->fieldDescription);
            self::assertSame($this->fieldDescription, $options['sonata_field_description']);
            self::assertSame($modelClass, $options['data_class']);
            self::assertFalse($options['btn_add']);
            self::assertFalse($options['delete']);
        }

        // collection type
        foreach ($collectionTypes as $formType) {
            $options = $this->formContractor->getDefaultOptions($formType, $this->fieldDescription, [
                'by_reference' => false,
            ]);
            self::assertSame($this->fieldDescription, $options['sonata_field_description']);
            self::assertSame(AdminType::class, $options['type']);
            self::assertTrue($options['modifiable']);
            self::assertSame($this->fieldDescription, $options['type_options']['sonata_field_description']);
            self::assertSame($modelClass, $options['type_options']['data_class']);
            self::assertFalse($options['type_options']['collection_by_reference']);
        }
    }

    public function testAdminClassAttachForFieldDescriptionWithAssociation(): void
    {
        $admin = $this->createMock(AdminInterface::class);

        $this->fieldDescription
            ->method('describesAssociation')
            ->willReturn(true);

        $this->fieldDescription
            ->method('getAdmin')
            ->willReturn($admin);

        // Then
        $admin
            ->expects(self::once())
            ->method('attachAdminClass')
            ->with($this->fieldDescription);

        // When
        $this->formContractor->fixFieldDescription($this->fieldDescription);
    }

    public function testAdminClassAttachForNotMappedField(): void
    {
        $admin = $this->createMock(AdminInterface::class);

        $this->fieldDescription
            ->method('describesAssociation')
            ->willReturn(false);
        $this->fieldDescription->method('getOption')->with(self::logicalOr(
            self::equalTo('edit'),
            self::equalTo('admin_code')
        ))->willReturn('sonata.admin.code');

        $this->fieldDescription
            ->method('getAdmin')
            ->willReturn($admin);

        // Then
        $admin
            ->expects(self::once())
            ->method('attachAdminClass')
            ->with($this->fieldDescription);

        // When
        $this->formContractor->fixFieldDescription($this->fieldDescription);
    }

    /**
     * @phpstan-param class-string $formType
     *
     * @dataProvider getFieldDescriptionValidationProvider
     */
    public function testThrowsExceptionWithInvalidFieldDescriptionInGetDefaultOptions(string $formType): void
    {
        $admin = $this->createStub(AdminInterface::class);
        $admin->method('getClass')->willReturn('Foo');

        $this->fieldDescription->method('getAdmin')->willReturn($admin);
        $this->fieldDescription->method('hasAssociationAdmin')->willReturn(false);
        $this->fieldDescription->method('describesSingleValuedAssociation')->willReturn(false);

        $this->expectException(\InvalidArgumentException::class);
        $this->formContractor->getDefaultOptions($formType, $this->fieldDescription);
    }

    /**
     * @phpstan-return iterable<array-key, array{0: class-string}>
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
