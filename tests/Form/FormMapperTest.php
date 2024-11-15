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

namespace Sonata\AdminBundle\Tests\Form;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Builder\FormContractorInterface;
use Sonata\AdminBundle\FieldDescription\BaseFieldDescription;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionFactoryInterface;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Security\Handler\SecurityHandlerInterface;
use Sonata\AdminBundle\Tests\Fixtures\Admin\CleanAdmin;
use Sonata\AdminBundle\Translator\NoopLabelTranslatorStrategy;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormFactoryInterface;

final class FormMapperTest extends TestCase
{
    private const DEFAULT_GRANTED_ROLE = 'ROLE_ADMIN_BAZ';

    /**
     * @var FormContractorInterface&MockObject
     */
    protected $contractor;

    /**
     * @var AdminInterface<object>
     */
    protected AdminInterface $admin;

    /**
     * @var FormMapper<object>
     */
    protected FormMapper $formMapper;

    protected function setUp(): void
    {
        $this->contractor = $this->createMock(FormContractorInterface::class);
        $this->contractor->method('getDefaultOptions')->willReturn([]);

        $formFactory = $this->createMock(FormFactoryInterface::class);
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        $formBuilder = new FormBuilder('test', \stdClass::class, $eventDispatcher, $formFactory);
        $formBuilder2 = new FormBuilder('test', \stdClass::class, $eventDispatcher, $formFactory);

        $formFactory->method('createNamedBuilder')->willReturn($formBuilder);
        $this->contractor->method('getFormBuilder')->willReturn($formBuilder2);

        $this->admin = new CleanAdmin();
        $this->admin->setModelClass(\stdClass::class);
        $this->admin->setSubject(new \stdClass());

        $modelManager = $this->createMock(ModelManagerInterface::class);
        $this->admin->setModelManager($modelManager);

        $securityHandler = $this->createStub(SecurityHandlerInterface::class);
        $securityHandler
            ->method('isGranted')
            ->willReturnCallback(static fn (AdminInterface $admin, string $attributes, ?object $object = null): bool => self::DEFAULT_GRANTED_ROLE === $attributes);

        $this->admin->setSecurityHandler($securityHandler);
        $this->admin->setFormContractor($this->contractor);

        $fieldDescriptionFactory = $this->createStub(FieldDescriptionFactoryInterface::class);
        $fieldDescriptionFactory
            ->method('create')
            ->willReturnCallback(function (string $class, string $name, array $options = []): FieldDescriptionInterface {
                $fieldDescription = $this->getFieldDescriptionMock($name);
                $fieldDescription->setOptions($options);

                return $fieldDescription;
            });

        $this->admin->setFieldDescriptionFactory($fieldDescriptionFactory);

        $this->admin->setLabelTranslatorStrategy(new NoopLabelTranslatorStrategy());

        $this->formMapper = new FormMapper(
            $this->contractor,
            $formBuilder,
            $this->admin
        );
    }

    public function testWithNoOptions(): void
    {
        $this->formMapper->with('foobar');

        static::assertSame(['default' => [
            'collapsed' => false,
            'class' => false,
            'description' => false,
            'label' => 'default',
            'translation_domain' => null,
            'name' => 'default',
            'box_class' => 'box box-primary',
            'empty_message' => 'message_form_group_empty',
            'empty_message_translation_domain' => 'SonataAdminBundle',
            'auto_created' => true,
            'groups' => ['foobar'],
            'tab' => true,
        ]], $this->admin->getFormTabs());

        static::assertSame(['foobar' => [
            'collapsed' => false,
            'class' => false,
            'description' => false,
            'label' => 'foobar',
            'translation_domain' => null,
            'name' => 'foobar',
            'box_class' => 'box box-primary',
            'empty_message' => 'message_form_group_empty',
            'empty_message_translation_domain' => 'SonataAdminBundle',
            'fields' => [],
        ]], $this->admin->getFormGroups());
    }

    public function testWithOptions(): void
    {
        $this->formMapper->with('foobar', [
            'translation_domain' => 'Foobar',
            'role' => self::DEFAULT_GRANTED_ROLE,
        ]);

        static::assertSame(['foobar' => [
            'collapsed' => false,
            'class' => false,
            'description' => false,
            'label' => 'foobar',
            'translation_domain' => 'Foobar',
            'name' => 'foobar',
            'box_class' => 'box box-primary',
            'empty_message' => 'message_form_group_empty',
            'empty_message_translation_domain' => 'SonataAdminBundle',
            'fields' => [],
            'role' => self::DEFAULT_GRANTED_ROLE,
        ]], $this->admin->getFormGroups());

        static::assertSame(['default' => [
            'collapsed' => false,
            'class' => false,
            'description' => false,
            'label' => 'default',
            'translation_domain' => 'Foobar',
            'name' => 'default',
            'box_class' => 'box box-primary',
            'empty_message' => 'message_form_group_empty',
            'empty_message_translation_domain' => 'SonataAdminBundle',
            'auto_created' => true,
            'groups' => ['foobar'],
            'tab' => true,
        ]], $this->admin->getFormTabs());
    }

    public function testWithFieldsCascadeTranslationDomain(): void
    {
        $this->contractor->expects(static::once())
            ->method('getDefaultOptions')
            ->willReturn([]);

        $this->formMapper
            ->with('foobar', [
                'translation_domain' => 'Foobar',
            ])
            ->add('foo', TextType::class)
        ->end();

        $fieldDescription = $this->admin->getFormFieldDescription('foo');
        static::assertSame('foo', $fieldDescription->getName());
        static::assertSame(TextType::class, $fieldDescription->getType());
        static::assertSame('Foobar', $fieldDescription->getTranslationDomain());

        static::assertTrue($this->formMapper->has('foo'));

        static::assertSame(['default' => [
            'collapsed' => false,
            'class' => false,
            'description' => false,
            'label' => 'default',
            'translation_domain' => 'Foobar',
            'name' => 'default',
            'box_class' => 'box box-primary',
            'empty_message' => 'message_form_group_empty',
            'empty_message_translation_domain' => 'SonataAdminBundle',
            'auto_created' => true,
            'groups' => ['foobar'],
            'tab' => true,
        ]], $this->admin->getFormTabs());

        static::assertSame(['foobar' => [
            'collapsed' => false,
            'class' => false,
            'description' => false,
            'label' => 'foobar',
            'translation_domain' => 'Foobar',
            'name' => 'foobar',
            'box_class' => 'box box-primary',
            'empty_message' => 'message_form_group_empty',
            'empty_message_translation_domain' => 'SonataAdminBundle',
            'fields' => [
                'foo' => 'foo',
            ],
        ]], $this->admin->getFormGroups());
    }

    public function testWithFieldsCascadeTranslationDomainFalse(): void
    {
        $this->contractor->expects(static::once())
            ->method('getDefaultOptions')
            ->willReturn([]);

        $this->formMapper->with('foobar', [
            'translation_domain' => false,
        ])
            ->add('foo', TextType::class)
            ->end();

        $fieldDescription = $this->admin->getFormFieldDescription('foo');
        static::assertSame('foo', $fieldDescription->getName());
        static::assertSame(TextType::class, $fieldDescription->getType());
        static::assertFalse($fieldDescription->getTranslationDomain());

        static::assertTrue($this->formMapper->has('foo'));

        static::assertSame(['default' => [
            'collapsed' => false,
            'class' => false,
            'description' => false,
            'label' => 'default',
            'translation_domain' => false,
            'name' => 'default',
            'box_class' => 'box box-primary',
            'empty_message' => 'message_form_group_empty',
            'empty_message_translation_domain' => 'SonataAdminBundle',
            'auto_created' => true,
            'groups' => ['foobar'],
            'tab' => true,
        ]], $this->admin->getFormTabs());

        static::assertSame(['foobar' => [
            'collapsed' => false,
            'class' => false,
            'description' => false,
            'label' => 'foobar',
            'translation_domain' => false,
            'name' => 'foobar',
            'box_class' => 'box box-primary',
            'empty_message' => 'message_form_group_empty',
            'empty_message_translation_domain' => 'SonataAdminBundle',
            'fields' => [
                'foo' => 'foo',
            ],
        ]], $this->admin->getFormGroups());
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testRemoveCascadeRemoveFieldFromFormGroup(): void
    {
        $this->formMapper->with('foo');
        $this->formMapper->remove('foo');
    }

    public function testIfTrueApply(): void
    {
        $this->contractor->expects(static::once())
            ->method('getDefaultOptions')
            ->willReturn([]);

        $this->formMapper
            ->ifTrue(true)
            ->add('foo', TextType::class)
            ->ifEnd();

        static::assertTrue($this->formMapper->has('foo'));
    }

    public function testIfTrueNotApply(): void
    {
        $this->formMapper
            ->ifTrue(false)
            ->add('foo', TextType::class)
            ->ifEnd();

        static::assertFalse($this->formMapper->has('foo'));
    }

    public function testIfTrueCombination(): void
    {
        $this->contractor->expects(static::once())
            ->method('getDefaultOptions')
            ->willReturn([]);

        $this->formMapper
            ->ifTrue(false)
            ->add('foo', TextType::class)
            ->ifEnd()
            ->add('baz', TextType::class);

        static::assertFalse($this->formMapper->has('foo'));
        static::assertTrue($this->formMapper->has('baz'));
    }

    public function testIfFalseApply(): void
    {
        $this->contractor->expects(static::once())
            ->method('getDefaultOptions')
            ->willReturn([]);

        $this->formMapper
            ->ifFalse(false)
            ->add('foo', TextType::class)
            ->ifEnd();

        static::assertTrue($this->formMapper->has('foo'));
    }

    public function testIfFalseNotApply(): void
    {
        $this->formMapper
            ->ifFalse(true)
            ->add('foo', TextType::class)
            ->ifEnd();

        static::assertFalse($this->formMapper->has('foo'));
    }

    public function testIfFalseCombination(): void
    {
        $this->contractor->expects(static::once())
            ->method('getDefaultOptions')
            ->willReturn([]);

        $this->formMapper
            ->ifFalse(true)
            ->add('foo', TextType::class)
            ->ifEnd()
            ->add('baz', TextType::class);

        static::assertFalse($this->formMapper->has('foo'));
        static::assertTrue($this->formMapper->has('baz'));
    }

    public function testIfTrueNested(): void
    {
        $this->formMapper
            ->ifTrue(true)
                ->ifTrue(true)
                    ->add('fooName')
                ->ifEnd()
            ->ifEnd();

        static::assertTrue($this->formMapper->has('fooName'));
    }

    public function testIfFalseNested(): void
    {
        $this->formMapper
            ->ifFalse(false)
                ->ifFalse(false)
                    ->add('fooName')
                ->ifEnd()
            ->ifEnd();

        static::assertTrue($this->formMapper->has('fooName'));
    }

    public function testIfCombinationNested(): void
    {
        $this->formMapper
            ->ifTrue(true)
                ->ifFalse(false)
                    ->add('fooName')
                ->ifEnd()
            ->ifEnd();

        static::assertTrue($this->formMapper->has('fooName'));
    }

    public function testIfFalseCombinationNested2(): void
    {
        $this->formMapper
            ->ifFalse(false)
                ->ifTrue(true)
                    ->add('fooName')
                ->ifEnd()
            ->ifEnd();

        static::assertTrue($this->formMapper->has('fooName'));
    }

    public function testIfFalseCombinationNested3(): void
    {
        $this->formMapper
            ->ifFalse(true)
                ->ifTrue(false)
                    ->add('fooName')
                ->ifEnd()
            ->ifEnd();

        static::assertFalse($this->formMapper->has('fooName'));
    }

    public function testIfFalseCombinationNested4(): void
    {
        $this->formMapper
            ->ifTrue(false)
                ->ifFalse(true)
                    ->add('fooName')
                ->ifEnd()
            ->ifEnd();

        static::assertFalse($this->formMapper->has('fooName'));
    }

    public function testGroupRemovingWithoutTab(): void
    {
        $this->formMapper->with('foobar');

        $this->formMapper->removeGroup('foobar');

        static::assertSame([], $this->admin->getFormGroups());
    }

    public function testGroupRemovingWithTab(): void
    {
        $this->formMapper->tab('mytab')->with('foobar');

        $this->formMapper->removeGroup('foobar', 'mytab');

        static::assertSame([], $this->admin->getFormGroups());
    }

    public function testGroupRemovingWithoutTabAndWithTabRemoving(): void
    {
        $this->formMapper->with('foobar');

        $this->formMapper->removeGroup('foobar', 'default', true);

        static::assertSame([], $this->admin->getFormGroups());
        static::assertSame([], $this->admin->getFormTabs());
    }

    public function testGroupRemovingWithTabAndWithTabRemoving(): void
    {
        $this->formMapper->tab('mytab')->with('foobar');

        $this->formMapper->removeGroup('foobar', 'mytab', true);

        static::assertSame([], $this->admin->getFormGroups());
        static::assertSame([], $this->admin->getFormTabs());
    }

    public function testTabRemoving(): void
    {
        $this->formMapper->tab('mytab')->with('foobar');

        $this->formMapper->removeTab('mytab');

        static::assertSame([], $this->admin->getFormGroups());
        static::assertSame([], $this->admin->getFormTabs());
    }

    public function testKeys(): void
    {
        $this->contractor
            ->method('getDefaultOptions')
            ->willReturn([]);

        $this->formMapper
            ->add('foo', TextType::class)
            ->add('baz', TextType::class);

        static::assertSame(['foo', 'baz'], $this->formMapper->keys());
    }

    public function testFieldNameIsSanitized(): void
    {
        $this->contractor
            ->method('getDefaultOptions')
            ->willReturn([]);

        $this->formMapper
            ->add('fo.o', TextType::class)
            ->add('ba__z', TextType::class);

        static::assertSame(['fo__o', 'ba____z'], $this->formMapper->keys());

        // FormFieldDescriptions have the original key.
        static::assertTrue($this->admin->hasFormFieldDescription('fo.o'));
        static::assertTrue($this->admin->hasFormFieldDescription('ba__z'));

        $formGroups = $this->admin->getFormGroups();

        static::assertArrayHasKey('default', $formGroups);
        static::assertIsArray($formGroups['default']);
        static::assertArrayHasKey('fields', $formGroups['default']);
        static::assertSame(['fo.o' => 'fo__o', 'ba__z' => 'ba____z'], $formGroups['default']['fields']);
    }

    public function testAddOptionRole(): void
    {
        $this->formMapper->add('bar', TextType::class);

        static::assertTrue($this->formMapper->has('bar'));
        static::assertTrue($this->admin->hasFormFieldDescription('bar'));

        $this->formMapper->add('quux', TextType::class, [], ['role' => 'ROLE_QUX']);

        static::assertTrue($this->formMapper->has('bar'));
        static::assertFalse($this->formMapper->has('quux'));

        $this->formMapper->end(); // Close default

        $this->formMapper
            ->with('qux')
                ->add('foobar', TextType::class, [], ['role' => self::DEFAULT_GRANTED_ROLE])
                ->add('foo', TextType::class, [], ['role' => 'ROLE_QUX'])
                ->add('baz', TextType::class)
            ->end();

        static::assertArrayHasKey('qux', $this->admin->getFormGroups());

        static::assertTrue($this->formMapper->has('foobar'));
        static::assertTrue($this->admin->hasFormFieldDescription('foobar'));

        static::assertFalse($this->formMapper->has('foo'));
        static::assertFalse($this->admin->hasFormFieldDescription('foo'));

        static::assertTrue($this->formMapper->has('baz'));
        static::assertTrue($this->admin->hasFormFieldDescription('baz'));
    }

    private function getFieldDescriptionMock(
        string $name,
        ?string $label = null,
        ?string $translationDomain = null,
    ): BaseFieldDescription {
        $fieldDescription = $this->getMockForAbstractClass(BaseFieldDescription::class, [$name, []]);

        if (null !== $label) {
            $fieldDescription->setOption('label', $label);
        }

        if (null !== $translationDomain) {
            $fieldDescription->setOption('translation_domain', $translationDomain);
        }

        return $fieldDescription;
    }
}
