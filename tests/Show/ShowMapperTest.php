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

    public function setUp()
    {
        $this->showBuilder = $this->getMockForAbstractClass(ShowBuilderInterface::class);
        $this->fieldDescriptionCollection = new FieldDescriptionCollection();
        $this->admin = $this->getMockForAbstractClass(AdminInterface::class);

        $this->admin->expects($this->any())
            ->method('getLabel')
            ->will($this->returnValue('AdminLabel'));

        $this->admin->expects($this->any())
            ->method('getShowTabs')
            ->will($this->returnValue([]));

        $this->groups = [];
        $this->listShowFields = [];

        $this->admin->expects($this->any())
            ->method('getShowGroups')
            ->will($this->returnCallback(function () {
                return $this->groups;
            }));

        $this->admin->expects($this->any())
            ->method('setShowGroups')
            ->will($this->returnCallback(function ($showGroups) {
                $this->groups = $showGroups;
            }));

        $this->admin->expects($this->any())
            ->method('reorderShowGroup')
            ->will($this->returnCallback(function ($group, $keys) {
                $this->groups[$group]['fields'] = array_merge(array_flip($keys), $this->groups[$group]['fields']);
            }));

        $modelManager = $this->getMockForAbstractClass(ModelManagerInterface::class);

        $modelManager->expects($this->any())
            ->method('getNewFieldDescriptionInstance')
            ->will($this->returnCallback(function ($class, $name, array $options = []) {
                $fieldDescription = $this->getFieldDescriptionMock();
                $fieldDescription->setName($name);
                $fieldDescription->setOptions($options);

                return $fieldDescription;
            }));

        $this->admin->expects($this->any())
            ->method('getModelManager')
            ->will($this->returnValue($modelManager));

        $labelTranslatorStrategy = new NoopLabelTranslatorStrategy();

        $this->admin->expects($this->any())
            ->method('getLabelTranslatorStrategy')
            ->will($this->returnValue($labelTranslatorStrategy));

        $this->admin->expects($this->any())
            ->method('hasShowFieldDescription')
            ->will($this->returnCallback(function ($name) {
                if (isset($this->listShowFields[$name])) {
                    return true;
                }
                $this->listShowFields[$name] = true;

                return false;
            }));

        $this->showBuilder->expects($this->any())
            ->method('addField')
            ->will($this->returnCallback(function ($list, $type, $fieldDescription, $admin) {
                $list->add($fieldDescription);
            }));

        $this->showMapper = new ShowMapper($this->showBuilder, $this->fieldDescriptionCollection, $this->admin);
    }

    public function testFluidInterface()
    {
        $fieldDescription = $this->getFieldDescriptionMock('fooName', 'fooLabel');

        $this->assertSame($this->showMapper, $this->showMapper->add($fieldDescription));
        $this->assertSame($this->showMapper, $this->showMapper->remove('fooName'));
        $this->assertSame($this->showMapper, $this->showMapper->reorder([]));
    }

    public function testGet()
    {
        $this->assertFalse($this->showMapper->has('fooName'));

        $fieldDescription = $this->getFieldDescriptionMock('fooName', 'fooLabel');

        $this->showMapper->add($fieldDescription);
        $this->assertSame($fieldDescription, $this->showMapper->get('fooName'));
    }

    public function testAdd()
    {
        $this->showMapper->add('fooName');

        $this->assertTrue($this->showMapper->has('fooName'));

        $fieldDescription = $this->showMapper->get('fooName');

        $this->assertInstanceOf(FieldDescriptionInterface::class, $fieldDescription);
        $this->assertSame('fooName', $fieldDescription->getName());
        $this->assertSame('fooName', $fieldDescription->getOption('label'));
    }

    public function testIfTrueApply()
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

    public function testIfTrueNotApply()
    {
        $this->showMapper->ifTrue(false);
        $this->showMapper->add('fooName');
        $this->showMapper->ifEnd();

        $this->assertFalse($this->showMapper->has('fooName'));
    }

    public function testIfTrueCombination()
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

    public function testIfFalseApply()
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

    public function testIfFalseNotApply()
    {
        $this->showMapper->ifFalse(true);
        $this->showMapper->add('fooName');
        $this->showMapper->ifEnd();

        $this->assertFalse($this->showMapper->has('fooName'));
    }

    public function testIfFalseCombination()
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

    public function testIfTrueNested()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot nest ifTrue or ifFalse call');

        $this->showMapper->ifTrue(true);
        $this->showMapper->ifTrue(true);
    }

    public function testIfFalseNested()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot nest ifTrue or ifFalse call');

        $this->showMapper->ifFalse(false);
        $this->showMapper->ifFalse(false);
    }

    public function testIfCombinationNested()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot nest ifTrue or ifFalse call');

        $this->showMapper->ifTrue(true);
        $this->showMapper->ifFalse(false);
    }

    public function testIfFalseCombinationNested2()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot nest ifTrue or ifFalse call');

        $this->showMapper->ifFalse(false);
        $this->showMapper->ifTrue(true);
    }

    public function testIfFalseCombinationNested3()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot nest ifTrue or ifFalse call');

        $this->showMapper->ifFalse(true);
        $this->showMapper->ifTrue(false);
    }

    public function testIfFalseCombinationNested4()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot nest ifTrue or ifFalse call');

        $this->showMapper->ifTrue(false);
        $this->showMapper->ifFalse(true);
    }

    public function testAddRemove()
    {
        $this->assertFalse($this->showMapper->has('fooName'));

        $fieldDescription = $this->getFieldDescriptionMock('fooName', 'fooLabel');

        $this->showMapper->add($fieldDescription);
        $this->assertTrue($this->showMapper->has('fooName'));

        $this->showMapper->remove('fooName');
        $this->assertFalse($this->showMapper->has('fooName'));
    }

    public function testAddException()
    {
        $this->expectException(\RuntimeException::class, 'invalid state');

        $this->showMapper->add(12345);
    }

    public function testAddDuplicateFieldNameException()
    {
        $name = 'name';
        $this->expectException(\RuntimeException::class, sprintf('Duplicate field %s "name" in show mapper. Names should be unique.', $name));

        $this->showMapper->add($name);
        $this->showMapper->add($name);
    }

    public function testKeys()
    {
        $fieldDescription1 = $this->getFieldDescriptionMock('fooName1', 'fooLabel1');
        $fieldDescription2 = $this->getFieldDescriptionMock('fooName2', 'fooLabel2');

        $this->showMapper->add($fieldDescription1);
        $this->showMapper->add($fieldDescription2);

        $this->assertSame(['fooName1', 'fooName2'], $this->showMapper->keys());
    }

    public function testReorder()
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

    public function testGroupRemovingWithoutTab()
    {
        $this->cleanShowMapper();

        $this->showMapper->with('groupfoo1');
        $this->showMapper->removeGroup('groupfoo1');

        $this->assertSame([], $this->admin->getShowGroups());
    }

    public function testGroupRemovingWithTab()
    {
        $this->cleanShowMapper();

        $this->showMapper->tab('mytab')->with('groupfoo2');
        $this->showMapper->removeGroup('groupfoo2', 'mytab');

        $this->assertSame([], $this->admin->getShowGroups());
    }

    public function testGroupRemovingWithoutTabAndWithTabRemoving()
    {
        $this->cleanShowMapper();

        $this->showMapper->with('groupfoo3');
        $this->showMapper->removeGroup('groupfoo3', 'default', true);

        $this->assertSame([], $this->admin->getShowGroups());
        $this->assertSame([], $this->admin->getShowTabs());
    }

    public function testGroupRemovingWithTabAndWithTabRemoving()
    {
        $this->cleanShowMapper();

        $this->showMapper->tab('mytab2')->with('groupfoo4');
        $this->showMapper->removeGroup('groupfoo4', 'mytab2', true);

        $this->assertSame([], $this->admin->getShowGroups());
        $this->assertSame([], $this->admin->getShowTabs());
    }

    public function testEmptyFieldLabel()
    {
        $this->showMapper->add('foo', null, ['label' => false]);

        $this->assertFalse($this->showMapper->get('foo')->getOption('label'));
    }

    private function cleanShowMapper()
    {
        $this->showBuilder = $this->getMockForAbstractClass(ShowBuilderInterface::class);
        $this->fieldDescriptionCollection = new FieldDescriptionCollection();
        $this->admin = new CleanAdmin('code', 'class', 'controller');
        $this->showMapper = new ShowMapper($this->showBuilder, $this->fieldDescriptionCollection, $this->admin);
    }

    private function getFieldDescriptionMock($name = null, $label = null)
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
