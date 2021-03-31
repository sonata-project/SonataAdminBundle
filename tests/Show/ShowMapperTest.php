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

namespace Sonata\AdminBundle\Tests\Show;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Builder\ShowBuilderInterface;
use Sonata\AdminBundle\FieldDescription\BaseFieldDescription;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionCollection;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionFactoryInterface;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Security\Handler\SecurityHandlerInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Tests\App\Builder\ShowBuilder;
use Sonata\AdminBundle\Tests\Fixtures\Admin\CleanAdmin;
use Sonata\AdminBundle\Translator\NoopLabelTranslatorStrategy;

/**
 * Test for ShowMapper.
 *
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class ShowMapperTest extends TestCase
{
    private const DEFAULT_GRANTED_ROLE = 'ROLE_ADMIN_BAZ';

    /**
     * @var ShowMapper
     */
    private $showMapper;

    /**
     * @var AdminInterface
     */
    private $admin;

    /**
     * @var ShowBuilderInterface
     */
    private $showBuilder;

    /**
     * @var FieldDescriptionCollection
     */
    private $fieldDescriptionCollection;

    /**
     * @var array
     */
    private $groups;

    /**
     * @var array
     */
    private $listShowFields;

    protected function setUp(): void
    {
        $this->showBuilder = $this->getMockForAbstractClass(ShowBuilderInterface::class);
        $this->fieldDescriptionCollection = new FieldDescriptionCollection();
        $this->admin = $this->createStub(AdminInterface::class);

        $this->admin
            ->method('getLabel')
            ->willReturn('AdminLabel');

        $this->admin
            ->method('getShowTabs')
            ->willReturn([]);

        $this->groups = [];
        $this->listShowFields = [];

        $this->admin
            ->method('getShowGroups')
            ->willReturnCallback(function () {
                return $this->groups;
            });

        $this->admin
            ->method('setShowGroups')
            ->willReturnCallback(function (array $showGroups): void {
                $this->groups = $showGroups;
            });

        $this->admin
            ->method('reorderShowGroup')
            ->willReturnCallback(function (string $group, array $keys): void {
                $this->groups[$group]['fields'] = array_merge(array_flip($keys), $this->groups[$group]['fields']);
            });

        $this->admin
            ->method('createFieldDescription')
            ->willReturnCallback(function (string $name, array $options = []): FieldDescriptionInterface {
                $fieldDescription = $this->getFieldDescriptionMock($name);
                $fieldDescription->setOptions($options);

                return $fieldDescription;
            });

        $labelTranslatorStrategy = new NoopLabelTranslatorStrategy();

        $this->admin
            ->method('getLabelTranslatorStrategy')
            ->willReturn($labelTranslatorStrategy);

        $this->admin
            ->method('hasShowFieldDescription')
            ->willReturnCallback(function (string $name): bool {
                if (isset($this->listShowFields[$name])) {
                    return true;
                }
                $this->listShowFields[$name] = true;

                return false;
            });

        $this->showBuilder
            ->method('addField')
            ->willReturnCallback(static function (
                FieldDescriptionCollection $list,
                ?string $type,
                FieldDescriptionInterface $fieldDescription
            ): void {
                $list->add($fieldDescription);
            });

        $this->showMapper = new ShowMapper($this->showBuilder, $this->fieldDescriptionCollection, $this->admin);
    }

    public function testFluidInterface(): void
    {
        $fieldDescription = $this->getFieldDescriptionMock('fooName', 'fooLabel');

        $this->assertSame($this->showMapper, $this->showMapper->add($fieldDescription));
        $this->assertSame($this->showMapper, $this->showMapper->remove('fooName'));
        $this->assertSame($this->showMapper, $this->showMapper->reorder([]));
    }

    public function testGet(): void
    {
        $this->assertFalse($this->showMapper->has('fooName'));

        $fieldDescription = $this->getFieldDescriptionMock('fooName', 'fooLabel');

        $this->showMapper->add($fieldDescription);
        $this->assertSame($fieldDescription, $this->showMapper->get('fooName'));
    }

    public function testAdd(): void
    {
        $this->showMapper->add('fooName');

        $this->assertTrue($this->showMapper->has('fooName'));

        $fieldDescription = $this->showMapper->get('fooName');

        $this->assertInstanceOf(FieldDescriptionInterface::class, $fieldDescription);
        $this->assertSame('fooName', $fieldDescription->getName());
        $this->assertSame('fooName', $fieldDescription->getOption('label'));
    }

    public function testIfTrueApply(): void
    {
        $this->showMapper->ifTrue(true);
        $this->showMapper->add('fooName');
        $this->showMapper->ifEnd();

        $this->assertTrue($this->showMapper->has('fooName'));
        $fieldDescription = $this->showMapper->get('fooName');

        $this->assertInstanceOf(FieldDescriptionInterface::class, $fieldDescription);
        $this->assertSame('fooName', $fieldDescription->getName());
        $this->assertSame('fooName', $fieldDescription->getOption('label'));
    }

    public function testIfTrueApplyWithTab(): void
    {
        $this->showMapper->ifTrue(true);
        $this->showMapper->tab('fooTab')->add('fooName')->end();
        $this->showMapper->ifEnd();

        $this->assertTrue($this->showMapper->has('fooName'));
        $fieldDescription = $this->showMapper->get('fooName');

        $this->assertInstanceOf(FieldDescriptionInterface::class, $fieldDescription);
        $this->assertSame('fooName', $fieldDescription->getName());
        $this->assertSame('fooName', $fieldDescription->getOption('label'));
    }

    public function testIfTrueNotApply(): void
    {
        $this->showMapper->ifTrue(false);
        $this->showMapper->add('fooName');
        $this->showMapper->ifEnd();

        $this->assertFalse($this->showMapper->has('fooName'));
    }

    public function testIfTrueNotApplyWithTab(): void
    {
        $this->showMapper->ifTrue(false);
        $this->showMapper->tab('fooTab')->add('fooName')->end();
        $this->showMapper->ifEnd();

        $this->assertFalse($this->showMapper->has('fooName'));
    }

    public function testIfTrueCombination(): void
    {
        $this->showMapper->ifTrue(false);
        $this->showMapper->add('fooName');
        $this->showMapper->ifEnd();
        $this->showMapper->add('barName');

        $this->assertFalse($this->showMapper->has('fooName'));
        $this->assertTrue($this->showMapper->has('barName'));
        $fieldDescription = $this->showMapper->get('barName');

        $this->assertInstanceOf(FieldDescriptionInterface::class, $fieldDescription);
        $this->assertSame('barName', $fieldDescription->getName());
        $this->assertSame('barName', $fieldDescription->getOption('label'));
    }

    public function testIfFalseApply(): void
    {
        $this->showMapper->ifFalse(false);
        $this->showMapper->add('fooName');
        $this->showMapper->ifEnd();

        $this->assertTrue($this->showMapper->has('fooName'));
        $fieldDescription = $this->showMapper->get('fooName');

        $this->assertInstanceOf(FieldDescriptionInterface::class, $fieldDescription);
        $this->assertSame('fooName', $fieldDescription->getName());
        $this->assertSame('fooName', $fieldDescription->getOption('label'));
    }

    public function testIfFalseApplyWithTab(): void
    {
        $this->showMapper->ifFalse(false);
        $this->showMapper->tab('fooTab')->add('fooName')->end();
        $this->showMapper->ifEnd();

        $this->assertTrue($this->showMapper->has('fooName'));
        $fieldDescription = $this->showMapper->get('fooName');

        $this->assertInstanceOf(FieldDescriptionInterface::class, $fieldDescription);
        $this->assertSame('fooName', $fieldDescription->getName());
        $this->assertSame('fooName', $fieldDescription->getOption('label'));
    }

    public function testIfFalseNotApply(): void
    {
        $this->showMapper->ifFalse(true);
        $this->showMapper->add('fooName');
        $this->showMapper->ifEnd();

        $this->assertFalse($this->showMapper->has('fooName'));
    }

    public function testIfFalseNotApplyWithTab(): void
    {
        $this->showMapper->ifFalse(true);
        $this->showMapper->tab('fooTab')->add('fooName')->end();
        $this->showMapper->ifEnd();

        $this->assertFalse($this->showMapper->has('fooName'));
    }

    public function testIfFalseCombination(): void
    {
        $this->showMapper->ifFalse(true);
        $this->showMapper->add('fooName');
        $this->showMapper->ifEnd();
        $this->showMapper->add('barName');

        $this->assertFalse($this->showMapper->has('fooName'));
        $this->assertTrue($this->showMapper->has('barName'));
        $fieldDescription = $this->showMapper->get('barName');

        $this->assertInstanceOf(FieldDescriptionInterface::class, $fieldDescription);
        $this->assertSame('barName', $fieldDescription->getName());
        $this->assertSame('barName', $fieldDescription->getOption('label'));
    }

    public function testIfTrueNested(): void
    {
        $this->showMapper
            ->ifTrue(true)
                ->ifTrue(true)
                    ->add('fooName')
                ->ifEnd()
            ->ifEnd();

        $this->assertTrue($this->showMapper->has('fooName'));
    }

    public function testIfFalseNested(): void
    {
        $this->showMapper
            ->ifFalse(false)
                ->ifFalse(false)
                    ->add('fooName')
                ->ifEnd()
            ->ifEnd();

        $this->assertTrue($this->showMapper->has('fooName'));
    }

    public function testIfCombinationNested(): void
    {
        $this->showMapper
            ->ifTrue(true)
                ->ifFalse(false)
                    ->add('fooName')
                ->ifEnd()
            ->ifEnd();

        $this->assertTrue($this->showMapper->has('fooName'));
    }

    public function testIfFalseCombinationNested2(): void
    {
        $this->showMapper
            ->ifFalse(false)
                ->ifTrue(true)
                    ->add('fooName')
                ->ifEnd()
            ->ifEnd();

        $this->assertTrue($this->showMapper->has('fooName'));
    }

    public function testIfFalseCombinationNested3(): void
    {
        $this->showMapper
            ->ifFalse(true)
                ->ifTrue(false)
                    ->add('fooName')
                ->ifEnd()
            ->ifEnd();

        $this->assertFalse($this->showMapper->has('fooName'));
    }

    public function testIfFalseCombinationNested4(): void
    {
        $this->showMapper
            ->ifTrue(false)
                ->ifFalse(true)
                    ->add('fooName')
                ->ifEnd()
            ->ifEnd();

        $this->assertFalse($this->showMapper->has('fooName'));
    }

    public function testAddRemove(): void
    {
        $this->assertFalse($this->showMapper->has('fooName'));

        $fieldDescription = $this->getFieldDescriptionMock('fooName', 'fooLabel');

        $this->showMapper->add($fieldDescription);
        $this->assertTrue($this->showMapper->has('fooName'));

        $this->showMapper->remove('fooName');
        $this->assertFalse($this->showMapper->has('fooName'));
    }

    public function testAddException(): void
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Unknown field name in show mapper. Field name should be either of FieldDescriptionInterface interface or string.');

        // @phpstan-ignore-next-line
        $this->showMapper->add(12345);
    }

    public function testAddDuplicateFieldNameException(): void
    {
        $name = 'name';
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(
            sprintf('Duplicate field %s "name" in show mapper. Names should be unique.', $name)
        );

        $this->showMapper->add($name);
        $this->showMapper->add($name);
    }

    public function testKeys(): void
    {
        $fieldDescription1 = $this->getFieldDescriptionMock('fooName1', 'fooLabel1');
        $fieldDescription2 = $this->getFieldDescriptionMock('fooName2', 'fooLabel2');

        $this->showMapper->add($fieldDescription1);
        $this->showMapper->add($fieldDescription2);

        $this->assertSame(['fooName1', 'fooName2'], $this->showMapper->keys());
    }

    public function testReorder(): void
    {
        $this->assertSame([], $this->admin->getShowGroups());

        $fieldDescription1 = $this->getFieldDescriptionMock('fooName1', 'fooLabel1');
        $fieldDescription2 = $this->getFieldDescriptionMock('fooName2', 'fooLabel2');
        $fieldDescription3 = $this->getFieldDescriptionMock('fooName3', 'fooLabel3');
        $fieldDescription4 = $this->getFieldDescriptionMock('fooName4', 'fooLabel4');

        $this->showMapper->with('Group1');
        $this->showMapper->add($fieldDescription1);
        $this->showMapper->add($fieldDescription2);
        $this->showMapper->add($fieldDescription3);
        $this->showMapper->add($fieldDescription4);

        $this->assertSame([
            'Group1' => [
                'collapsed' => false,
                'class' => false,
                'description' => false,
                'label' => 'Group1',
                'translation_domain' => null,
                'name' => 'Group1',
                'box_class' => 'box box-primary',
                'empty_message' => 'message_form_group_empty',
                'empty_message_translation_domain' => 'SonataAdminBundle',
                'fields' => ['fooName1' => 'fooName1', 'fooName2' => 'fooName2', 'fooName3' => 'fooName3', 'fooName4' => 'fooName4'],
            ], ], $this->admin->getShowGroups());

        $this->showMapper->reorder(['fooName3', 'fooName2', 'fooName1', 'fooName4']);

        // print_r is used to compare order of items in associative arrays
        $this->assertSame(print_r([
            'Group1' => [
                'collapsed' => false,
                'class' => false,
                'description' => false,
                'label' => 'Group1',
                'translation_domain' => null,
                'name' => 'Group1',
                'box_class' => 'box box-primary',
                'empty_message' => 'message_form_group_empty',
                'empty_message_translation_domain' => 'SonataAdminBundle',
                'fields' => ['fooName3' => 'fooName3', 'fooName2' => 'fooName2', 'fooName1' => 'fooName1', 'fooName4' => 'fooName4'],
            ], ], true), print_r($this->admin->getShowGroups(), true));
    }

    public function testGroupRemovingWithoutTab(): void
    {
        $this->cleanShowMapper();

        $this->showMapper->with('groupfoo1');
        $this->showMapper->removeGroup('groupfoo1');

        $this->assertSame([], $this->admin->getShowGroups());
    }

    public function testGroupRemovingWithTab(): void
    {
        $this->cleanShowMapper();

        $this->showMapper->tab('mytab')->with('groupfoo2');
        $this->showMapper->removeGroup('groupfoo2', 'mytab');

        $this->assertSame([], $this->admin->getShowGroups());
    }

    public function testGroupRemovingWithoutTabAndWithTabRemoving(): void
    {
        $this->cleanShowMapper();

        $this->showMapper->with('groupfoo3');
        $this->showMapper->removeGroup('groupfoo3', 'default', true);

        $this->assertSame([], $this->admin->getShowGroups());
        $this->assertSame([], $this->admin->getShowTabs());
    }

    public function testGroupRemovingWithTabAndWithTabRemoving(): void
    {
        $this->cleanShowMapper();

        $this->showMapper->tab('mytab2')->with('groupfoo4');
        $this->showMapper->removeGroup('groupfoo4', 'mytab2', true);

        $this->assertSame([], $this->admin->getShowGroups());
        $this->assertSame([], $this->admin->getShowTabs());
    }

    public function testTabRemoving(): void
    {
        $this->cleanShowMapper();

        $this->showMapper->tab('mytab2')->with('groupfoo4');
        $this->showMapper->removeTab('mytab2');

        $this->assertSame([], $this->admin->getShowGroups());
        $this->assertSame([], $this->admin->getShowTabs());
    }

    public function testEmptyFieldLabel(): void
    {
        $this->showMapper->add('foo', null, ['label' => false]);

        $this->assertFalse($this->showMapper->get('foo')->getOption('label'));

        $this->showMapper->add('bar', null, ['label' => null]);

        $this->assertSame('bar', $this->showMapper->get('bar')->getOption('label'));
    }

    public function testAddOptionRole(): void
    {
        $this->cleanShowMapper();

        $this->showMapper->add('bar', 'bar');

        $this->assertTrue($this->showMapper->has('bar'));

        $this->showMapper->add('quux', 'bar', ['role' => 'ROLE_QUX']);

        $this->assertTrue($this->showMapper->has('bar'));
        $this->assertFalse($this->showMapper->has('quux'));

        $this->showMapper->end(); // Close default

        $this->showMapper
            ->with('qux')
                ->add('foobar', 'bar', ['role' => self::DEFAULT_GRANTED_ROLE])
                ->add('foo', 'bar', ['role' => 'ROLE_QUX'])
                ->add('baz', 'bar')
            ->end();

        $this->assertArrayHasKey('qux', $this->admin->getShowGroups());
        $this->assertTrue($this->showMapper->has('foobar'));
        $this->assertFalse($this->showMapper->has('foo'));
        $this->assertTrue($this->showMapper->has('baz'));
    }

    private function cleanShowMapper(): void
    {
        $this->showBuilder = $this->getMockForAbstractClass(ShowBuilderInterface::class);
        $this->showBuilder
            ->method('addField')
            ->willReturnCallback(static function (FieldDescriptionCollection $list, ?string $type, FieldDescriptionInterface $fieldDescription): void {
                $list->add($fieldDescription);
            });
        $this->fieldDescriptionCollection = new FieldDescriptionCollection();
        $this->admin = new CleanAdmin('code', 'class', 'controller');
        $securityHandler = $this->createMock(SecurityHandlerInterface::class);
        $securityHandler
            ->method('isGranted')
            ->willReturnCallback(static function (AdminInterface $admin, string $attributes, $object = null): bool {
                return self::DEFAULT_GRANTED_ROLE === $attributes;
            });

        $this->admin->setSecurityHandler($securityHandler);

        $this->showMapper = new ShowMapper($this->showBuilder, $this->fieldDescriptionCollection, $this->admin);

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

        $this->admin->setShowBuilder(new ShowBuilder());
    }

    private function getFieldDescriptionMock(string $name, ?string $label = null): BaseFieldDescription
    {
        $fieldDescription = $this->getMockForAbstractClass(BaseFieldDescription::class, [$name, []]);

        if (null !== $label) {
            $fieldDescription->setOption('label', $label);
        }

        return $fieldDescription;
    }
}
