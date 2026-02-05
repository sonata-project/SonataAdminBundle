<?php

declare(strict_types=1);

/*
 * This file is part of sensiolabs-de/admin-bundle.
 *
 * (c) SensioLabs Deutschland <info@sensiolabs.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\AdminBundle\Tests\Builder;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SensioLabs\AdminBundle\Admin\AdminInterface;
use SensioLabs\AdminBundle\Builder\AbstractFormContractor;
use SensioLabs\AdminBundle\Builder\FormContractorInterface;
use SensioLabs\AdminBundle\FieldDescription\FieldDescriptionInterface;
use SensioLabs\AdminBundle\Form\Type\AdminType;
use SensioLabs\AdminBundle\Form\Type\ModelAutocompleteType;
use SensioLabs\AdminBundle\Form\Type\ModelHiddenType;
use SensioLabs\AdminBundle\Form\Type\ModelListType;
use SensioLabs\AdminBundle\Form\Type\ModelType;
use SensioLabs\AdminBundle\Model\ModelManagerInterface;
use SensioLabs\AdminBundle\Tests\Fixtures\Form\MyCustomType;
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
    private FormFactoryInterface $formFactory;

    private FormContractorInterface $formContractor;

    /**
     * @var MockObject&FieldDescriptionInterface
     */
    private FieldDescriptionInterface $fieldDescription;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fieldDescription = $this->createMock(FieldDescriptionInterface::class);

        $this->formFactory = $this->createMock(FormFactoryInterface::class);
        $formRegistry = static::createStub(FormRegistryInterface::class);
        $formRegistry->method('getType')->willReturnCallback(static function (string $type): ResolvedFormTypeInterface {
            $resolvedType = static::createStub(ResolvedFormTypeInterface::class);
            if (MyCustomType::class === $type) {
                $parentType = static::createStub(ResolvedFormTypeInterface::class);
                $parentType->method('getInnerType')->willReturn(new ModelType(static::createStub(PropertyAccessor::class)));
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
        $this->formFactory->expects(static::once())->method('createNamedBuilder')
            ->willReturn($this->createMock(FormBuilderInterface::class));

        static::assertInstanceOf(
            FormBuilderInterface::class,
            $this->formContractor->getFormBuilder('test', ['foo' => 'bar'])
        );
    }

    public function testDefaultOptionsForSonataFormTypes(): void
    {
        $admin = $this->createMock(AdminInterface::class);
        $modelClass = 'FooModel';

        $modelManager = static::createStub(ModelManagerInterface::class);
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
            MyCustomType::class,
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
            static::assertSame($this->fieldDescription, $options['sonata_field_description']);
            static::assertSame($modelClass, $options['class']);
            static::assertSame($modelManager, $options['model_manager']);
        }

        // admin type
        $this->fieldDescription
            ->method('describesSingleValuedAssociation')
            ->willReturn(true);
        foreach ($adminTypes as $formType) {
            $options = $this->formContractor->getDefaultOptions($formType, $this->fieldDescription);
            static::assertSame($this->fieldDescription, $options['sonata_field_description']);
            static::assertSame($modelClass, $options['data_class']);
            static::assertFalse($options['btn_add']);
            static::assertFalse($options['delete']);
        }

        // collection type
        foreach ($collectionTypes as $formType) {
            $options = $this->formContractor->getDefaultOptions($formType, $this->fieldDescription, [
                'by_reference' => false,
            ]);
            static::assertSame($this->fieldDescription, $options['sonata_field_description']);
            static::assertSame(AdminType::class, $options['type']);
            static::assertTrue($options['modifiable']);
            static::assertIsArray($options['type_options']);
            static::assertSame($this->fieldDescription, $options['type_options']['sonata_field_description']);
            static::assertSame($modelClass, $options['type_options']['data_class']);
            static::assertFalse($options['type_options']['collection_by_reference']);
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
            ->expects(static::once())
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
        $this->fieldDescription->method('getOption')->with(static::logicalOr(
            static::equalTo('edit'),
            static::equalTo('admin_code')
        ))->willReturn('sensiolabs.admin.code');

        $this->fieldDescription
            ->method('getAdmin')
            ->willReturn($admin);

        // Then
        $admin
            ->expects(static::once())
            ->method('attachAdminClass')
            ->with($this->fieldDescription);

        // When
        $this->formContractor->fixFieldDescription($this->fieldDescription);
    }

    /**
     * @phpstan-param class-string $formType
     */
    #[DataProvider('provideThrowsExceptionWithInvalidFieldDescriptionInGetDefaultOptionsCases')]
    public function testThrowsExceptionWithInvalidFieldDescriptionInGetDefaultOptions(string $formType): void
    {
        $admin = static::createStub(AdminInterface::class);
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
    public static function provideThrowsExceptionWithInvalidFieldDescriptionInGetDefaultOptionsCases(): iterable
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
