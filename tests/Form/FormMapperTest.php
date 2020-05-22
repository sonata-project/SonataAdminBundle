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

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\BaseFieldDescription;
use Sonata\AdminBundle\Builder\FormContractorInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Security\Handler\SecurityHandlerInterface;
use Sonata\AdminBundle\Tests\Fixtures\Admin\CleanAdmin;
use Sonata\AdminBundle\Translator\LabelTranslatorStrategyInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\ResolvedFormTypeInterface;

class FormMapperTest extends TestCase
{
    private const DEFAULT_GRANTED_ROLE = 'ROLE_ADMIN_BAZ';

    /**
     * @var FormContractorInterface
     */
    protected $contractor;

    /**
     * @var AdminInterface
     */
    protected $admin;

    /**
     * @var ModelManagerInterface
     */
    protected $modelManager;

    /**
     * @var FormMapper
     */
    protected $formMapper;

    protected function setUp(): void
    {
        $this->contractor = $this->createMock(FormContractorInterface::class);

        $formFactory = $this->createMock(FormFactoryInterface::class);
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        $formBuilder = new FormBuilder('test', \stdClass::class, $eventDispatcher, $formFactory);

        $this->admin = new CleanAdmin('code', 'class', 'controller');

        // NEXT_MAJOR: Remove the calls to `setFormGroups()` and `setFormTabs()`
        $this->admin->setFormGroups([]);
        $this->admin->setFormTabs([]);

        $securityHandler = $this->createMock(SecurityHandlerInterface::class);
        $securityHandler
            ->method('isGranted')
            ->willReturnCallback(static function (AdminInterface $admin, string $attributes, $object = null): bool {
                return self::DEFAULT_GRANTED_ROLE === $attributes;
            });

        $this->admin->setSecurityHandler($securityHandler);

        $this->modelManager = $this->getMockForAbstractClass(ModelManagerInterface::class);

        $this->modelManager
            ->method('getNewFieldDescriptionInstance')
            ->willReturnCallback(function (string $class, string $name, array $options = []): BaseFieldDescription {
                $fieldDescription = $this->getFieldDescriptionMock();
                $fieldDescription->setName($name);
                $fieldDescription->setOptions($options);

                return $fieldDescription;
            });

        $this->admin->setModelManager($this->modelManager);

        $labelTranslatorStrategy = $this->getMockForAbstractClass(LabelTranslatorStrategyInterface::class);
        $this->admin->setLabelTranslatorStrategy($labelTranslatorStrategy);

        $this->formMapper = new FormMapper(
            $this->contractor,
            $formBuilder,
            $this->admin
        );
    }

    public function testWithNoOptions(): void
    {
        $this->formMapper->with('foobar');

        $this->assertSame(['default' => [
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

        $this->assertSame(['foobar' => [
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

        $this->assertSame(['foobar' => [
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

        $this->assertSame(['default' => [
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
        $this->contractor->expects($this->once())
            ->method('getDefaultOptions')
            ->willReturn([]);

        $this->formMapper->with('foobar', [
                'translation_domain' => 'Foobar',
            ])
            ->add('foo', 'bar')
        ->end();

        $fieldDescription = $this->admin->getFormFieldDescription('foo');
        $this->assertSame('foo', $fieldDescription->getName());
        $this->assertSame('bar', $fieldDescription->getType());
        $this->assertSame('Foobar', $fieldDescription->getTranslationDomain());

        $this->assertTrue($this->formMapper->has('foo'));

        $this->assertSame(['default' => [
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

        $this->assertSame(['foobar' => [
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
        $this->contractor->expects($this->once())
            ->method('getDefaultOptions')
            ->willReturn([]);

        $this->formMapper
            ->ifTrue(true)
            ->add('foo', 'bar')
            ->ifEnd()
        ;

        $this->assertTrue($this->formMapper->has('foo'));
    }

    public function testIfTrueNotApply(): void
    {
        $this->formMapper
            ->ifTrue(false)
            ->add('foo', 'bar')
            ->ifEnd()
        ;

        $this->assertFalse($this->formMapper->has('foo'));
    }

    public function testIfTrueCombination(): void
    {
        $this->contractor->expects($this->once())
            ->method('getDefaultOptions')
            ->willReturn([]);

        $this->formMapper
            ->ifTrue(false)
            ->add('foo', 'bar')
            ->ifEnd()
            ->add('baz', 'foobaz')
        ;

        $this->assertFalse($this->formMapper->has('foo'));
        $this->assertTrue($this->formMapper->has('baz'));
    }

    public function testIfFalseApply(): void
    {
        $this->contractor->expects($this->once())
            ->method('getDefaultOptions')
            ->willReturn([]);

        $this->formMapper
            ->ifFalse(false)
            ->add('foo', 'bar')
            ->ifEnd()
        ;

        $this->assertTrue($this->formMapper->has('foo'));
    }

    public function testIfFalseNotApply(): void
    {
        $this->formMapper
            ->ifFalse(true)
            ->add('foo', 'bar')
            ->ifEnd()
        ;

        $this->assertFalse($this->formMapper->has('foo'));
    }

    public function testIfFalseCombination(): void
    {
        $this->contractor->expects($this->once())
            ->method('getDefaultOptions')
            ->willReturn([]);

        $this->formMapper
            ->ifFalse(true)
            ->add('foo', 'bar')
            ->ifEnd()
            ->add('baz', 'foobaz')
        ;

        $this->assertFalse($this->formMapper->has('foo'));
        $this->assertTrue($this->formMapper->has('baz'));
    }

    public function testIfTrueNested(): void
    {
        $this->formMapper
            ->ifTrue(true)
                ->ifTrue(true)
                    ->add('fooName')
                ->ifEnd()
            ->ifEnd()
        ;

        $this->assertTrue($this->formMapper->has('fooName'));
    }

    public function testIfFalseNested(): void
    {
        $this->formMapper
            ->ifFalse(false)
                ->ifFalse(false)
                    ->add('fooName')
                ->ifEnd()
            ->ifEnd()
        ;

        $this->assertTrue($this->formMapper->has('fooName'));
    }

    public function testIfCombinationNested(): void
    {
        $this->formMapper
            ->ifTrue(true)
                ->ifFalse(false)
                    ->add('fooName')
                ->ifEnd()
            ->ifEnd()
        ;

        $this->assertTrue($this->formMapper->has('fooName'));
    }

    public function testIfFalseCombinationNested2(): void
    {
        $this->formMapper
            ->ifFalse(false)
                ->ifTrue(true)
                    ->add('fooName')
                ->ifEnd()
            ->ifEnd()
        ;

        $this->assertTrue($this->formMapper->has('fooName'));
    }

    public function testIfFalseCombinationNested3(): void
    {
        $this->formMapper
            ->ifFalse(true)
                ->ifTrue(false)
                    ->add('fooName')
                ->ifEnd()
            ->ifEnd()
        ;

        $this->assertFalse($this->formMapper->has('fooName'));
    }

    public function testIfFalseCombinationNested4(): void
    {
        $this->formMapper
            ->ifTrue(false)
                ->ifFalse(true)
                    ->add('fooName')
                ->ifEnd()
            ->ifEnd()
        ;

        $this->assertFalse($this->formMapper->has('fooName'));
    }

    public function testAddAcceptFormBuilder(): void
    {
        $formBuilder = $this
            ->getMockBuilder(FormBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $formBuilder
            ->method('getName')
            ->willReturn('foo');

        $formType = $this
            ->getMockBuilder(ResolvedFormTypeInterface::class)
            ->getMock();

        $innerType = $this
            ->getMockBuilder(FormType::class)
            ->getMock();

        $formType->expects($this->once())
            ->method('getInnerType')
            ->willReturn($innerType);

        $formBuilder->expects($this->once())
            ->method('getType')
            ->willReturn($formType);

        $this->formMapper->add($formBuilder);
        $this->assertSame($this->formMapper->get('foo'), $formBuilder);
    }

    public function testAddFormBuilderWithType(): void
    {
        $formBuilder = $this
            ->getMockBuilder(FormBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $formBuilder
            ->method('getName')
            ->willReturn('foo');

        $formBuilder->expects($this->never())
            ->method('getType');

        $this->formMapper->add($formBuilder, FormType::class);
        $this->assertSame($this->formMapper->get('foo'), $formBuilder);
    }

    public function testGroupRemovingWithoutTab(): void
    {
        $this->formMapper->with('foobar');

        $this->formMapper->removeGroup('foobar');

        $this->assertSame([], $this->admin->getFormGroups());
    }

    public function testGroupRemovingWithTab(): void
    {
        $this->formMapper->tab('mytab')->with('foobar');

        $this->formMapper->removeGroup('foobar', 'mytab');

        $this->assertSame([], $this->admin->getFormGroups());
    }

    public function testGroupRemovingWithoutTabAndWithTabRemoving(): void
    {
        $this->formMapper->with('foobar');

        $this->formMapper->removeGroup('foobar', 'default', true);

        $this->assertSame([], $this->admin->getFormGroups());
        $this->assertSame([], $this->admin->getFormTabs());
    }

    public function testGroupRemovingWithTabAndWithTabRemoving(): void
    {
        $this->formMapper->tab('mytab')->with('foobar');

        $this->formMapper->removeGroup('foobar', 'mytab', true);

        $this->assertSame([], $this->admin->getFormGroups());
        $this->assertSame([], $this->admin->getFormTabs());
    }

    public function testKeys(): void
    {
        $this->contractor
            ->method('getDefaultOptions')
            ->willReturn([]);

        $this->formMapper
            ->add('foo', 'bar')
            ->add('baz', 'foobaz')
        ;

        $this->assertSame(['foo', 'baz'], $this->formMapper->keys());
    }

    public function testFieldNameIsSanitized(): void
    {
        $this->contractor
            ->method('getDefaultOptions')
            ->willReturn([]);

        $this->formMapper
            ->add('fo.o', 'bar')
            ->add('ba__z', 'foobaz')
        ;

        $this->assertSame(['fo__o', 'ba____z'], $this->formMapper->keys());
    }

    public function testAddOptionRole(): void
    {
        $this->formMapper->add('bar', 'bar');

        $this->assertTrue($this->formMapper->has('bar'));

        $this->formMapper->add('quux', 'bar', [], ['role' => 'ROLE_QUX']);

        $this->assertTrue($this->formMapper->has('bar'));
        $this->assertFalse($this->formMapper->has('quux'));

        $this->formMapper
            ->with('qux')
                ->add('foobar', 'bar', [], ['role' => self::DEFAULT_GRANTED_ROLE])
                ->add('foo', 'bar', [], ['role' => 'ROLE_QUX'])
                ->add('baz', 'bar')
            ->end();

        $this->assertArrayHasKey('qux', $this->admin->getFormGroups());
        $this->assertTrue($this->formMapper->has('foobar'));
        $this->assertFalse($this->formMapper->has('foo'));
        $this->assertTrue($this->formMapper->has('baz'));
    }

    private function getFieldDescriptionMock(
        ?string $name = null,
        ?string $label = null,
        ?string $translationDomain = null
    ): BaseFieldDescription {
        $fieldDescription = $this->getMockForAbstractClass(BaseFieldDescription::class);

        if (null !== $name) {
            $fieldDescription->setName($name);
        }

        if (null !== $label) {
            $fieldDescription->setOption('label', $label);
        }

        if (null !== $translationDomain) {
            $fieldDescription->setOption('translation_domain', $translationDomain);
        }

        return $fieldDescription;
    }
}
