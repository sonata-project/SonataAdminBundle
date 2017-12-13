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
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\BaseFieldDescription;
use Sonata\AdminBundle\Admin\FieldDescriptionCollection;
use Sonata\AdminBundle\Builder\DatagridBuilderInterface;
use Sonata\AdminBundle\Datagrid\Datagrid;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\PagerInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Filter\Filter;
use Sonata\AdminBundle\Filter\FilterInterface;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilder;

/**
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class DatagridMapperTest extends TestCase
{
    /**
     * @var DatagridMapper
     */
    private $datagridMapper;

    /**
     * @var Datagrid
     */
    private $datagrid;

    public function setUp(): void
    {
        $datagridBuilder = $this->createMock(DatagridBuilderInterface::class);

        $proxyQuery = $this->createMock(ProxyQueryInterface::class);
        $pager = $this->createMock(PagerInterface::class);
        $fieldDescriptionCollection = $this->createMock(FieldDescriptionCollection::class);
        $formBuilder = $this->getMockBuilder(FormBuilder::class)
                     ->disableOriginalConstructor()
                     ->getMock();

        $this->datagrid = new Datagrid($proxyQuery, $fieldDescriptionCollection, $pager, $formBuilder, []);

        $admin = $this->createMock(AdminInterface::class);

        $datagridBuilder->expects($this->any())
            ->method('addFilter')
            ->will($this->returnCallback(function ($datagrid, $type, $fieldDescription, $admin): void {
                $fieldDescription->setType($type);

                $filter = $this->getMockForAbstractClass(Filter::class);

                $filter->expects($this->any())
                    ->method('getDefaultOptions')
                    ->will($this->returnValue(['foo_default_option' => 'bar_default']));

                $filter->initialize($fieldDescription->getName(), $fieldDescription->getOptions());
                $datagrid->addFilter($filter);
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

        $admin->expects($this->any())
            ->method('getModelManager')
            ->will($this->returnValue($modelManager));

        $this->datagridMapper = new DatagridMapper($datagridBuilder, $this->datagrid, $admin);
    }

    public function testFluidInterface(): void
    {
        $fieldDescription = $this->getFieldDescriptionMock('fooName', 'fooLabel');

        $this->assertSame($this->datagridMapper, $this->datagridMapper->add($fieldDescription, null, ['field_name' => 'fooFilterName']));
        $this->assertSame($this->datagridMapper, $this->datagridMapper->remove('fooName'));
        $this->assertSame($this->datagridMapper, $this->datagridMapper->reorder([]));
    }

    public function testGet(): void
    {
        $this->assertFalse($this->datagridMapper->has('fooName'));

        $fieldDescription = $this->getFieldDescriptionMock('foo.name', 'fooLabel');

        $this->datagridMapper->add($fieldDescription, null, ['field_name' => 'fooFilterName']);

        $filter = $this->datagridMapper->get('foo.name');
        $this->assertInstanceOf(FilterInterface::class, $filter);
        $this->assertSame('foo.name', $filter->getName());
        $this->assertSame('foo__name', $filter->getFormName());
        $this->assertSame(TextType::class, $filter->getFieldType());
        $this->assertSame('fooLabel', $filter->getLabel());
        $this->assertSame(['required' => false], $filter->getFieldOptions());
        $this->assertSame([
            'show_filter' => null,
            'advanced_filter' => true,
            'foo_default_option' => 'bar_default',
            'label' => 'fooLabel',
            'field_name' => 'fooFilterName',
            'placeholder' => 'short_object_description_placeholder',
            'link_parameters' => [],
        ], $filter->getOptions());
    }

    public function testGet2(): void
    {
        $this->assertFalse($this->datagridMapper->has('fooName'));

        $fieldDescription = $this->getFieldDescriptionMock('fooName', 'fooLabel');

        $this->datagridMapper->add($fieldDescription, 'foo_type', ['field_name' => 'fooFilterName', 'foo_filter_option' => 'foo_filter_option_value', 'foo_default_option' => 'bar_custom'], 'foo_field_type', ['foo_field_option' => 'baz']);

        $filter = $this->datagridMapper->get('fooName');
        $this->assertInstanceOf(FilterInterface::class, $filter);
        $this->assertSame('fooName', $filter->getName());
        $this->assertSame('fooName', $filter->getFormName());
        $this->assertSame('foo_field_type', $filter->getFieldType());
        $this->assertSame('fooLabel', $filter->getLabel());
        $this->assertSame(['foo_field_option' => 'baz'], $filter->getFieldOptions());
        $this->assertSame([
            'show_filter' => null,
            'advanced_filter' => true,
            'foo_default_option' => 'bar_custom',
            'label' => 'fooLabel',
            'field_name' => 'fooFilterName',
            'foo_filter_option' => 'foo_filter_option_value',
            'field_options' => ['foo_field_option' => 'baz'],
            'field_type' => 'foo_field_type',
            'placeholder' => 'short_object_description_placeholder',
            'link_parameters' => [],
        ], $filter->getOptions());
    }

    public function testAdd(): void
    {
        $this->datagridMapper->add('fooName');

        $this->assertTrue($this->datagridMapper->has('fooName'));

        $fieldDescription = $this->datagridMapper->get('fooName');

        $this->assertInstanceOf(FilterInterface::class, $fieldDescription);
        $this->assertSame('fooName', $fieldDescription->getName());
    }

    public function testAddWithoutFieldName(): void
    {
        $this->datagridMapper->add('foo.bar');

        $this->assertTrue($this->datagridMapper->has('foo.bar'));

        $fieldDescription = $this->datagridMapper->get('foo.bar');

        $this->assertInstanceOf(FilterInterface::class, $fieldDescription);
        $this->assertSame('foo.bar', $fieldDescription->getName());
        $this->assertSame('bar', $fieldDescription->getOption('field_name'));
    }

    public function testAddRemove(): void
    {
        $this->assertFalse($this->datagridMapper->has('fooName'));

        $fieldDescription = $this->getFieldDescriptionMock('fooName', 'fooLabel');

        $this->datagridMapper->add($fieldDescription, null, ['field_name' => 'fooFilterName']);
        $this->assertTrue($this->datagridMapper->has('fooName'));

        $this->datagridMapper->remove('fooName');
        $this->assertFalse($this->datagridMapper->has('fooName'));
        $this->assertSame('fooFilterName', $fieldDescription->getOption('field_name'));
    }

    public function testAddException(): void
    {
        $this->expectException(\RuntimeException::class, 'Unknown field name in datagrid mapper. Field name should be either of FieldDescriptionInterface interface or string');

        $this->datagridMapper->add(12345);
    }

    public function testAddDuplicateNameException(): void
    {
        $tmpNames = [];
        $this->datagridMapper->getAdmin()
            ->expects($this->exactly(2))
            ->method('hasFilterFieldDescription')
            ->will($this->returnCallback(function ($name) use (&$tmpNames) {
                if (isset($tmpNames[$name])) {
                    return true;
                }
                $tmpNames[$name] = $name;

                return false;
            }));

        $this->expectException(\RuntimeException::class, 'Duplicate field name "fooName" in datagrid mapper. Names should be unique.');

        $this->datagridMapper->add('fooName');
        $this->datagridMapper->add('fooName');
    }

    public function testKeys(): void
    {
        $fieldDescription1 = $this->getFieldDescriptionMock('fooName1', 'fooLabel1');
        $fieldDescription2 = $this->getFieldDescriptionMock('fooName2', 'fooLabel2');

        $this->datagridMapper->add($fieldDescription1, null, ['field_name' => 'fooFilterName1']);
        $this->datagridMapper->add($fieldDescription2, null, ['field_name' => 'fooFilterName2']);

        $this->assertSame(['fooName1', 'fooName2'], $this->datagridMapper->keys());
    }

    public function testReorder(): void
    {
        $fieldDescription1 = $this->getFieldDescriptionMock('fooName1', 'fooLabel1');
        $fieldDescription2 = $this->getFieldDescriptionMock('fooName2', 'fooLabel2');
        $fieldDescription3 = $this->getFieldDescriptionMock('fooName3', 'fooLabel3');
        $fieldDescription4 = $this->getFieldDescriptionMock('fooName4', 'fooLabel4');

        $this->datagridMapper->add($fieldDescription1, null, ['field_name' => 'fooFilterName1']);
        $this->datagridMapper->add($fieldDescription2, null, ['field_name' => 'fooFilterName2']);
        $this->datagridMapper->add($fieldDescription3, null, ['field_name' => 'fooFilterName3']);
        $this->datagridMapper->add($fieldDescription4, null, ['field_name' => 'fooFilterName4']);

        $this->assertSame([
            'fooName1',
            'fooName2',
            'fooName3',
            'fooName4',
        ], array_keys($this->datagrid->getFilters()));

        $this->datagridMapper->reorder(['fooName3', 'fooName2', 'fooName1', 'fooName4']);

        $this->assertSame([
            'fooName3',
            'fooName2',
            'fooName1',
            'fooName4',
        ], array_keys($this->datagrid->getFilters()));
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
