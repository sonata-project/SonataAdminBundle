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

namespace Sonata\AdminBundle\Tests\Datagrid;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\BaseFieldDescription;
use Sonata\AdminBundle\Admin\FieldDescriptionCollection;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Builder\ListBuilderInterface;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Translator\NoopLabelTranslatorStrategy;

/**
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class ListMapperTest extends TestCase
{
    /**
     * @var ListMapper
     */
    private $listMapper;

    /**
     * @var FieldDescriptionCollection
     */
    private $fieldDescriptionCollection;

    /**
     * @var AdminInterface
     */
    private $admin;

    public function setUp()
    {
        $listBuilder = $this->createMock(ListBuilderInterface::class);
        $this->fieldDescriptionCollection = new FieldDescriptionCollection();
        $this->admin = $this->createMock(AbstractAdmin::class);

        $listBuilder->expects($this->any())
            ->method('addField')
            ->will($this->returnCallback(function ($list, $type, $fieldDescription, $admin) {
                $list->add($fieldDescription);
            }));

        $modelManager = $this->createMock(ModelManagerInterface::class);

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

        $this->listMapper = new ListMapper($listBuilder, $this->fieldDescriptionCollection, $this->admin);
    }

    public function testFluidInterface()
    {
        $fieldDescription = $this->getFieldDescriptionMock('fooName', 'fooLabel');

        $this->assertSame($this->listMapper, $this->listMapper->add($fieldDescription));
        $this->assertSame($this->listMapper, $this->listMapper->remove('fooName'));
        $this->assertSame($this->listMapper, $this->listMapper->reorder([]));
    }

    public function testGet()
    {
        $this->assertFalse($this->listMapper->has('fooName'));

        $fieldDescription = $this->getFieldDescriptionMock('fooName', 'fooLabel');

        $this->listMapper->add($fieldDescription);
        $this->assertSame($fieldDescription, $this->listMapper->get('fooName'));
    }

    public function testAddIdentifier()
    {
        $this->assertFalse($this->listMapper->has('fooName'));

        $fieldDescription = $this->getFieldDescriptionMock('fooName', 'fooLabel');

        $this->listMapper->addIdentifier($fieldDescription);
        $this->assertTrue($this->listMapper->has('fooName'));
    }

    public function testAdd()
    {
        $this->listMapper->add('fooName');
        $this->listMapper->add('fooNameLabelBar', null, ['label' => 'Foo Bar']);
        $this->listMapper->add('fooNameLabelFalse', null, ['label' => false]);

        $this->assertTrue($this->listMapper->has('fooName'));

        $fieldDescription = $this->listMapper->get('fooName');
        $fieldLabelBar = $this->listMapper->get('fooNameLabelBar');
        $fieldLabelFalse = $this->listMapper->get('fooNameLabelFalse');

        $this->assertInstanceOf(FieldDescriptionInterface::class, $fieldDescription);
        $this->assertSame('fooName', $fieldDescription->getName());
        $this->assertSame('fooName', $fieldDescription->getOption('label'));
        $this->assertSame('Foo Bar', $fieldLabelBar->getOption('label'));
        $this->assertFalse($fieldLabelFalse->getOption('label'));
    }

    /**
     * @group legacy
     */
    public function testLegacyAddViewInlineAction()
    {
        $this->assertFalse($this->listMapper->has('_action'));
        $this->listMapper->add('_action', 'actions', ['actions' => ['view' => []]]);

        $this->assertTrue($this->listMapper->has('_action'));

        $fieldDescription = $this->listMapper->get('_action');

        $this->assertInstanceOf(FieldDescriptionInterface::class, $fieldDescription);
        $this->assertSame('_action', $fieldDescription->getName());
        $this->assertCount(1, $fieldDescription->getOption('actions'));
        $this->assertSame(['show' => []], $fieldDescription->getOption('actions'));
    }

    public function testAddViewInlineAction()
    {
        $this->assertFalse($this->listMapper->has('_action'));
        $this->listMapper->add('_action', 'actions', ['actions' => ['show' => []]]);

        $this->assertTrue($this->listMapper->has('_action'));

        $fieldDescription = $this->listMapper->get('_action');

        $this->assertInstanceOf(FieldDescriptionInterface::class, $fieldDescription);
        $this->assertSame('_action', $fieldDescription->getName());
        $this->assertCount(1, $fieldDescription->getOption('actions'));
        $this->assertSame(['show' => []], $fieldDescription->getOption('actions'));
    }

    public function testAddRemove()
    {
        $this->assertFalse($this->listMapper->has('fooName'));

        $fieldDescription = $this->getFieldDescriptionMock('fooName', 'fooLabel');

        $this->listMapper->add($fieldDescription);
        $this->assertTrue($this->listMapper->has('fooName'));

        $this->listMapper->remove('fooName');
        $this->assertFalse($this->listMapper->has('fooName'));
    }

    public function testAddDuplicateNameException()
    {
        $tmpNames = [];
        $this->admin->expects($this->any())
            ->method('hasListFieldDescription')
            ->will($this->returnCallback(function ($name) use (&$tmpNames) {
                if (isset($tmpNames[$name])) {
                    return true;
                }
                $tmpNames[$name] = $name;

                return false;
            }));

        $this->expectException(\RuntimeException::class, 'Duplicate field name "fooName" in list mapper. Names should be unique.');

        $this->listMapper->add('fooName');
        $this->listMapper->add('fooName');
    }

    public function testAddWrongTypeException()
    {
        $this->expectException(\RuntimeException::class, 'Unknown field name in list mapper. Field name should be either of FieldDescriptionInterface interface or string.');

        $this->listMapper->add(12345);
    }

    public function testAutoAddVirtualOption()
    {
        foreach (['actions', 'batch', 'select'] as $type) {
            $this->listMapper->add('_'.$type, $type);
        }

        foreach ($this->fieldDescriptionCollection->getElements() as $field) {
            $this->assertTrue(
                $field->isVirtual(),
                'Failed asserting that FieldDescription with type "'.$field->getType().'" is tagged with virtual flag.'
            );
        }
    }

    public function testKeys()
    {
        $fieldDescription1 = $this->getFieldDescriptionMock('fooName1', 'fooLabel1');
        $fieldDescription2 = $this->getFieldDescriptionMock('fooName2', 'fooLabel2');

        $this->listMapper->add($fieldDescription1);
        $this->listMapper->add($fieldDescription2);

        $this->assertSame(['fooName1', 'fooName2'], $this->listMapper->keys());
    }

    public function testReorder()
    {
        $fieldDescription1 = $this->getFieldDescriptionMock('fooName1', 'fooLabel1');
        $fieldDescription2 = $this->getFieldDescriptionMock('fooName2', 'fooLabel2');
        $fieldDescription3 = $this->getFieldDescriptionMock('fooName3', 'fooLabel3');
        $fieldDescription4 = $this->getFieldDescriptionMock('fooName4', 'fooLabel4');

        $this->listMapper->add($fieldDescription1);
        $this->listMapper->add($fieldDescription2);
        $this->listMapper->add($fieldDescription3);
        $this->listMapper->add($fieldDescription4);

        $this->assertSame([
            'fooName1' => $fieldDescription1,
            'fooName2' => $fieldDescription2,
            'fooName3' => $fieldDescription3,
            'fooName4' => $fieldDescription4,
        ], $this->fieldDescriptionCollection->getElements());

        $this->listMapper->reorder(['fooName3', 'fooName2', 'fooName1', 'fooName4']);

        // print_r is used to compare order of items in associative arrays
        $this->assertSame(print_r([
            'fooName3' => $fieldDescription3,
            'fooName2' => $fieldDescription2,
            'fooName1' => $fieldDescription1,
            'fooName4' => $fieldDescription4,
        ], true), print_r($this->fieldDescriptionCollection->getElements(), true));
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
