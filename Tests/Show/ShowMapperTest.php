<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Show;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionCollection;
use Sonata\AdminBundle\Builder\ShowBuilderInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Tests\Fixtures\Admin\CleanAdmin;
use Sonata\AdminBundle\Translator\NoopLabelTranslatorStrategy;

/**
 * Test for ShowMapper.
 *
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class ShowMapperTest extends \PHPUnit_Framework_TestCase
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
        $this->showBuilder = $this->getMock('Sonata\AdminBundle\Builder\ShowBuilderInterface');
        $this->fieldDescriptionCollection = new FieldDescriptionCollection();
        $this->admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');

        $this->admin->expects($this->any())
            ->method('getLabel')
            ->will($this->returnValue('AdminLabel'));

        $this->admin->expects($this->any())
            ->method('getShowTabs')
            ->will($this->returnValue(array()));

        $this->groups = array();
        $this->listShowFields = array();

        // php 5.3 BC
        $groups = &$this->groups;
        $listShowFields = &$this->listShowFields;

        $this->admin->expects($this->any())
            ->method('getShowGroups')
            ->will($this->returnCallback(function () use (&$groups) {
                return $groups;
            }));

        $this->admin->expects($this->any())
            ->method('setShowGroups')
            ->will($this->returnCallback(function ($showGroups) use (&$groups) {
                $groups = $showGroups;
            }));

        $this->admin->expects($this->any())
            ->method('reorderShowGroup')
            ->will($this->returnCallback(function ($group, $keys) use (&$groups) {
                $showGroups = $groups;
                $showGroups[$group]['fields'] = array_merge(array_flip($keys), $showGroups[$group]['fields']);
                $groups = $showGroups;
            }));

        $modelManager = $this->getMock('Sonata\AdminBundle\Model\ModelManagerInterface');

        // php 5.3 BC
        $fieldDescription = $this->getFieldDescriptionMock();

        $modelManager->expects($this->any())
            ->method('getNewFieldDescriptionInstance')
            ->will($this->returnCallback(function ($class, $name, array $options = array()) use ($fieldDescription) {
                $fieldDescriptionClone = clone $fieldDescription;
                $fieldDescriptionClone->setName($name);
                $fieldDescriptionClone->setOptions($options);

                return $fieldDescriptionClone;
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
            ->will($this->returnCallback(function ($name) use (&$listShowFields) {
                if (isset($listShowFields[$name])) {
                    return true;
                }
                $listShowFields[$name] = true;

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
        $this->assertSame($this->showMapper, $this->showMapper->reorder(array()));
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

        $this->assertInstanceOf('Sonata\AdminBundle\Admin\FieldDescriptionInterface', $fieldDescription);
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

        $this->assertInstanceOf('Sonata\AdminBundle\Admin\FieldDescriptionInterface', $fieldDescription);
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

        $this->assertInstanceOf('Sonata\AdminBundle\Admin\FieldDescriptionInterface', $fieldDescription);
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

        $this->assertInstanceOf('Sonata\AdminBundle\Admin\FieldDescriptionInterface', $fieldDescription);
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

        $this->assertInstanceOf('Sonata\AdminBundle\Admin\FieldDescriptionInterface', $fieldDescription);
        $this->assertSame('barName', $fieldDescription->getName());
        $this->assertSame('barName', $fieldDescription->getOption('label'));
    }

    /**
     * @expectedException        RuntimeException
     * @expectedExceptionMessage Cannot nest ifTrue or ifFalse call
     */
    public function testIfTrueNested()
    {
        $this->showMapper->ifTrue(true);
        $this->showMapper->ifTrue(true);
    }

    /**
     * @expectedException        RuntimeException
     * @expectedExceptionMessage Cannot nest ifTrue or ifFalse call
     */
    public function testIfFalseNested()
    {
        $this->showMapper->ifFalse(false);
        $this->showMapper->ifFalse(false);
    }

    /**
     * @expectedException        RuntimeException
     * @expectedExceptionMessage Cannot nest ifTrue or ifFalse call
     */
    public function testIfCombinationNested()
    {
        $this->showMapper->ifTrue(true);
        $this->showMapper->ifFalse(false);
    }

    /**
     * @expectedException        RuntimeException
     * @expectedExceptionMessage Cannot nest ifTrue or ifFalse call
     */
    public function testIfFalseCombinationNested2()
    {
        $this->showMapper->ifFalse(false);
        $this->showMapper->ifTrue(true);
    }

    /**
     * @expectedException        RuntimeException
     * @expectedExceptionMessage Cannot nest ifTrue or ifFalse call
     */
    public function testIfFalseCombinationNested3()
    {
        $this->showMapper->ifFalse(true);
        $this->showMapper->ifTrue(false);
    }

    /**
     * @expectedException        RuntimeException
     * @expectedExceptionMessage Cannot nest ifTrue or ifFalse call
     */
    public function testIfFalseCombinationNested4()
    {
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
        try {
            $this->showMapper->add(12345);
        } catch (\RuntimeException $e) {
            $this->assertContains('invalid state', $e->getMessage());

            return;
        }

        $this->fail('Failed asserting that exception of type "\RuntimeException" is thrown.');
    }

    public function testAddDuplicateFieldNameException()
    {
        $name = 'name';

        try {
            $this->showMapper->add($name);
            $this->showMapper->add($name);
        } catch (\RuntimeException $e) {
            $this->assertContains(sprintf('Duplicate field name "%s" in show mapper. Names should be unique.', $name), $e->getMessage());

            return;
        }

        $this->fail('Failed asserting that duplicate field name exception of type "\RuntimeException" is thrown.');
    }

    public function testKeys()
    {
        $fieldDescription1 = $this->getFieldDescriptionMock('fooName1', 'fooLabel1');
        $fieldDescription2 = $this->getFieldDescriptionMock('fooName2', 'fooLabel2');

        $this->showMapper->add($fieldDescription1);
        $this->showMapper->add($fieldDescription2);

        $this->assertSame(array('fooName1', 'fooName2'), $this->showMapper->keys());
    }

    public function testReorder()
    {
        $this->assertSame(array(), $this->admin->getShowGroups());

        $fieldDescription1 = $this->getFieldDescriptionMock('fooName1', 'fooLabel1');
        $fieldDescription2 = $this->getFieldDescriptionMock('fooName2', 'fooLabel2');
        $fieldDescription3 = $this->getFieldDescriptionMock('fooName3', 'fooLabel3');
        $fieldDescription4 = $this->getFieldDescriptionMock('fooName4', 'fooLabel4');

        $this->showMapper->with('Group1');
        $this->showMapper->add($fieldDescription1);
        $this->showMapper->add($fieldDescription2);
        $this->showMapper->add($fieldDescription3);
        $this->showMapper->add($fieldDescription4);

        $this->assertSame(array(
            'Group1' => array(
                'collapsed' => false,
                'class' => false,
                'description' => false,
                'translation_domain' => null,
                'name' => 'Group1',
                'box_class' => 'box box-primary',
                'fields' => array('fooName1' => 'fooName1', 'fooName2' => 'fooName2', 'fooName3' => 'fooName3', 'fooName4' => 'fooName4'),
            ), ), $this->admin->getShowGroups());

        $this->showMapper->reorder(array('fooName3', 'fooName2', 'fooName1', 'fooName4'));

        // print_r is used to compare order of items in associative arrays
        $this->assertSame(print_r(array(
            'Group1' => array(
                'collapsed' => false,
                'class' => false,
                'description' => false,
                'translation_domain' => null,
                'name' => 'Group1',
                'box_class' => 'box box-primary',
                'fields' => array('fooName3' => 'fooName3', 'fooName2' => 'fooName2', 'fooName1' => 'fooName1', 'fooName4' => 'fooName4'),
            ), ), true), print_r($this->admin->getShowGroups(), true));
    }

    public function testGroupRemovingWithoutTab()
    {
        $this->cleanShowMapper();

        $this->showMapper->with('groupfoo1');
        $this->showMapper->removeGroup('groupfoo1');

        $this->assertSame(array(), $this->admin->getShowGroups());
    }

    public function testGroupRemovingWithTab()
    {
        $this->cleanShowMapper();

        $this->showMapper->tab('mytab')->with('groupfoo2');
        $this->showMapper->removeGroup('groupfoo2', 'mytab');

        $this->assertSame(array(), $this->admin->getShowGroups());
    }

    public function testGroupRemovingWithoutTabAndWithTabRemoving()
    {
        $this->cleanShowMapper();

        $this->showMapper->with('groupfoo3');
        $this->showMapper->removeGroup('groupfoo3', 'default', true);

        $this->assertSame(array(), $this->admin->getShowGroups());
        $this->assertSame(array(), $this->admin->getShowTabs());
    }

    public function testGroupRemovingWithTabAndWithTabRemoving()
    {
        $this->cleanShowMapper();

        $this->showMapper->tab('mytab2')->with('groupfoo4');
        $this->showMapper->removeGroup('groupfoo4', 'mytab2', true);

        $this->assertSame(array(), $this->admin->getShowGroups());
        $this->assertSame(array(), $this->admin->getShowTabs());
    }

    private function cleanShowMapper()
    {
        $this->showBuilder = $this->getMock('Sonata\AdminBundle\Builder\ShowBuilderInterface');
        $this->fieldDescriptionCollection = new FieldDescriptionCollection();
        $this->admin = new CleanAdmin('code', 'class', 'controller');
        $this->showMapper = new ShowMapper($this->showBuilder, $this->fieldDescriptionCollection, $this->admin);
    }

    private function getFieldDescriptionMock($name = null, $label = null)
    {
        $fieldDescription = $this->getMockForAbstractClass('Sonata\AdminBundle\Admin\BaseFieldDescription');

        if ($name !== null) {
            $fieldDescription->setName($name);
        }

        if ($label !== null) {
            $fieldDescription->setOption('label', $label);
        }

        return $fieldDescription;
    }
}
