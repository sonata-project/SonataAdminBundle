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
use Sonata\AdminBundle\Admin\BaseFieldDescription;
use Sonata\AdminBundle\Admin\FieldDescriptionCollection;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Builder\ShowBuilderInterface;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Tests\Fixtures\Admin\CleanAdmin;
use Sonata\AdminBundle\Translator\NoopLabelTranslatorStrategy;

/**
 * Test for ShowMapper.
 *
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class ShowMapperTest extends TestCase
{
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

    public function setUp(): void
    {
        $this->showBuilder = $this->getMockForAbstractClass(ShowBuilderInterface::class);
        $this->fieldDescriptionCollection = new FieldDescriptionCollection();
        $this->admin = $this->getMockForAbstractClass(AdminInterface::class);

        $this->admin->expects($this->any())
            ->method('getLabel')
            ->willReturn('AdminLabel');

        $this->admin->expects($this->any())
            ->method('getShowTabs')
            ->willReturn([]);

        $this->groups = [];
        $this->listShowFields = [];

        $this->admin->expects($this->any())
            ->method('getShowGroups')
            ->willReturnCallback(function () {
                return $this->groups;
            });

        $this->admin->expects($this->any())
            ->method('setShowGroups')
            ->willReturnCallback(function ($showGroups): void {
                $this->groups = $showGroups;
            });

        $this->admin->expects($this->any())
            ->method('reorderShowGroup')
            ->willReturnCallback(function ($group, $keys): void {
                $this->groups[$group]['fields'] = array_merge(array_flip($keys), $this->groups[$group]['fields']);
            });

        $modelManager = $this->getMockForAbstractClass(ModelManagerInterface::class);

        $modelManager->expects($this->any())
            ->method('getNewFieldDescriptionInstance')
            ->willReturnCallback(function ($class, $name, array $options = []) {
                $fieldDescription = $this->getFieldDescriptionMock();
                $fieldDescription->setName($name);
                $fieldDescription->setOptions($options);

                return $fieldDescription;
            });

        $this->admin->expects($this->any())
            ->method('getModelManager')
            ->willReturn($modelManager);

        $labelTranslatorStrategy = new NoopLabelTranslatorStrategy();

        $this->admin->expects($this->any())
            ->method('getLabelTranslatorStrategy')
            ->willReturn($labelTranslatorStrategy);

        $this->admin->expects($this->any())
            ->method('hasShowFieldDescription')
            ->willReturnCallback(function ($name) {
                if (isset($this->listShowFields[$name])) {
                    return true;
                }
                $this->listShowFields[$name] = true;

                return false;
            });

        $this->showBuilder->expects($this->any())
            ->method('addField')
            ->willReturnCallback(static function ($list, $type, $fieldDescription, $admin): void {
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

    public function testIfTrueNotApply(): void
    {
        $this->showMapper->ifTrue(false);
        $this->showMapper->add('fooName');
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

    public function testIfFalseNotApply(): void
    {
        $this->showMapper->ifFalse(true);
        $this->showMapper->add('fooName');
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
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot nest ifTrue or ifFalse call');

        $this->showMapper->ifTrue(true);
        $this->showMapper->ifTrue(true);
    }

    public function testIfFalseNested(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot nest ifTrue or ifFalse call');

        $this->showMapper->ifFalse(false);
        $this->showMapper->ifFalse(false);
    }

    public function testIfCombinationNested(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot nest ifTrue or ifFalse call');

        $this->showMapper->ifTrue(true);
        $this->showMapper->ifFalse(false);
    }

    public function testIfFalseCombinationNested2(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot nest ifTrue or ifFalse call');

        $this->showMapper->ifFalse(false);
        $this->showMapper->ifTrue(true);
    }

    public function testIfFalseCombinationNested3(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot nest ifTrue or ifFalse call');

        $this->showMapper->ifFalse(true);
        $this->showMapper->ifTrue(false);
    }

    public function testIfFalseCombinationNested4(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot nest ifTrue or ifFalse call');

        $this->showMapper->ifTrue(false);
        $this->showMapper->ifFalse(true);
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
        $this->expectException(\RuntimeException::class, 'invalid state');

        $this->showMapper->add(12345);
    }

    public function testAddDuplicateFieldNameException(): void
    {
        $name = 'name';
        $this->expectException(\RuntimeException::class, sprintf('Duplicate field %s "name" in show mapper. Names should be unique.', $name));

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

    public function testEmptyFieldLabel(): void
    {
        $this->showMapper->add('foo', null, ['label' => false]);

        $this->assertFalse($this->showMapper->get('foo')->getOption('label'));
    }

    private function cleanShowMapper(): void
    {
        $this->showBuilder = $this->getMockForAbstractClass(ShowBuilderInterface::class);
        $this->fieldDescriptionCollection = new FieldDescriptionCollection();
        $this->admin = new CleanAdmin('code', 'class', 'controller');
        $this->showMapper = new ShowMapper($this->showBuilder, $this->fieldDescriptionCollection, $this->admin);
    }

    private function getFieldDescriptionMock(?string $name = null, ?string $label = null): BaseFieldDescription
    {
        $fieldDescription = $this->getMockForAbstractClass(BaseFieldDescription::class);

        if (null !== $name) {
            $fieldDescription->setName($name);
        }

        if (null !== $label) {
            $fieldDescription->setOption('label', $label);
        }

        return $fieldDescription;
    }
}
