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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Builder\DatagridBuilderInterface;
use Sonata\AdminBundle\Datagrid\Datagrid;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\PagerInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\FieldDescription\BaseFieldDescription;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionCollection;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Filter\Filter;
use Sonata\AdminBundle\Filter\FilterInterface;
use Sonata\AdminBundle\Translator\LabelTranslatorStrategyInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilder;

/**
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class DatagridMapperTest extends TestCase
{
    private const DEFAULT_GRANTED_ROLE = 'ROLE_ADMIN_BAZ';

    /**
     * @var DatagridMapper<object>
     */
    private $datagridMapper;

    /**
     * @var Datagrid<ProxyQueryInterface>
     */
    private $datagrid;

    /**
     * @var AdminInterface<object>&MockObject
     */
    private $admin;

    protected function setUp(): void
    {
        $datagridBuilder = $this->createMock(DatagridBuilderInterface::class);

        /** @var ProxyQueryInterface $proxyQuery */
        $proxyQuery = $this->createMock(ProxyQueryInterface::class);
        $pager = $this->createMock(PagerInterface::class);
        $fieldDescriptionCollection = new FieldDescriptionCollection();
        $formBuilder = $this->getMockBuilder(FormBuilder::class)
                     ->disableOriginalConstructor()
                     ->getMock();

        $this->datagrid = new Datagrid($proxyQuery, $fieldDescriptionCollection, $pager, $formBuilder, []);

        $this->admin = $this->createMock(AdminInterface::class);

        $datagridBuilder
            ->method('addFilter')
            ->willReturnCallback(function (
                Datagrid $datagrid,
                ?string $type,
                FieldDescriptionInterface $fieldDescription
            ): void {
                $fieldDescription->setType($type);

                $filter = $this->getMockForAbstractClass(Filter::class);

                $filter
                    ->method('getDefaultOptions')
                    ->willReturn(['foo_default_option' => 'bar_default']);

                $filter->initialize($fieldDescription->getName(), $fieldDescription->getOptions());
                $datagrid->addFilter($filter);
            });

        $this->admin
            ->method('createFieldDescription')
            ->willReturnCallback(function (string $name, array $options = []): FieldDescriptionInterface {
                $fieldDescription = $this->getMockForAbstractClass(BaseFieldDescription::class, [$name, []]);
                $fieldDescription->setOptions($options);

                return $fieldDescription;
            });

        $this->admin
            ->method('isGranted')
            ->willReturnCallback(static function (string $name, ?object $object = null): bool {
                return self::DEFAULT_GRANTED_ROLE === $name;
            });

        $labelTranslatorStrategy = $this->createStub(LabelTranslatorStrategyInterface::class);
        $labelTranslatorStrategy->method('getLabel')->willReturnCallback(
            static function ($label, $context = '', $type = ''): string {
                return sprintf('%s.%s_%s', $context, $type, $label);
            }
        );

        $this->admin
            ->method('getLabelTranslatorStrategy')
            ->willReturn($labelTranslatorStrategy);

        $this->datagridMapper = new DatagridMapper($datagridBuilder, $this->datagrid, $this->admin);
    }

    public function testFluidInterface(): void
    {
        $this->assertSame($this->datagridMapper, $this->datagridMapper->add('fooName', null, ['field_name' => 'fooFilterName']));
        $this->assertSame($this->datagridMapper, $this->datagridMapper->remove('fooName'));
        $this->assertSame($this->datagridMapper, $this->datagridMapper->reorder([]));
    }

    public function testGet(): void
    {
        $this->assertFalse($this->datagridMapper->has('fooName'));

        $this->datagridMapper->add('foo.name', null, [
            'field_name' => 'fooFilterName',
            'label' => 'fooLabel',
        ]);

        $filter = $this->datagridMapper->get('foo.name');
        $this->assertInstanceOf(FilterInterface::class, $filter);
        $this->assertSame('foo.name', $filter->getName());
        $this->assertSame('foo__name', $filter->getFormName());
        $this->assertSame(TextType::class, $filter->getFieldType());
        $this->assertSame('fooLabel', $filter->getLabel());
        $this->assertSame([], $filter->getFieldOptions());
        $this->assertSame([
            'show_filter' => null,
            'advanced_filter' => true,
            'foo_default_option' => 'bar_default',
            'field_name' => 'fooFilterName',
            'label' => 'fooLabel',
        ], $filter->getOptions());
    }

    public function testGet2(): void
    {
        $this->assertFalse($this->datagridMapper->has('fooName'));

        $this->datagridMapper->add('fooName', \stdClass::class, [
            'label' => 'fooLabel',
            'field_name' => 'fooFilterName',
            'field_type' => 'foo_field_type',
            'field_options' => ['foo_field_option' => 'baz'],
            'foo_filter_option' => 'foo_filter_option_value',
            'foo_default_option' => 'bar_custom',
        ]);

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
            'field_type' => 'foo_field_type',
            'field_options' => ['foo_field_option' => 'baz'],
            'foo_filter_option' => 'foo_filter_option_value',
        ], $filter->getOptions());
    }

    public function testAdd(): void
    {
        $this->datagridMapper->add('fooName');

        $this->assertTrue($this->datagridMapper->has('fooName'));

        $filter = $this->datagridMapper->get('fooName');

        $this->assertInstanceOf(FilterInterface::class, $filter);
        $this->assertSame('fooName', $filter->getName());
        $this->assertSame('filter.label_fooName', $filter->getLabel());
    }

    public function testAddWithoutFieldName(): void
    {
        $this->datagridMapper->add('foo.bar');

        $this->assertTrue($this->datagridMapper->has('foo.bar'));

        $filter = $this->datagridMapper->get('foo.bar');

        $this->assertInstanceOf(FilterInterface::class, $filter);
        $this->assertSame('foo.bar', $filter->getName());
        $this->assertNull($filter->getOption('field_name'));
    }

    public function testAddRemove(): void
    {
        $this->assertFalse($this->datagridMapper->has('fooName'));

        $this->datagridMapper->add('fooName', null, ['field_name' => 'fooFilterName']);
        $this->assertTrue($this->datagridMapper->has('fooName'));

        $this->datagridMapper->remove('fooName');
        $this->assertFalse($this->datagridMapper->has('fooName'));
    }

    public function testAddDuplicateNameException(): void
    {
        $tmpNames = [];
        $this->admin
            ->expects($this->exactly(2))
            ->method('hasFilterFieldDescription')
            ->willReturnCallback(static function (string $name) use (&$tmpNames): bool {
                if (isset($tmpNames[$name])) {
                    return true;
                }
                $tmpNames[$name] = $name;

                return false;
            });

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Duplicate field name "fooName" in datagrid mapper. Names should be unique.');

        $this->datagridMapper->add('fooName');
        $this->datagridMapper->add('fooName');
    }

    public function testKeys(): void
    {
        $this->datagridMapper->add('fooName1', null, ['field_name' => 'fooFilterName1']);
        $this->datagridMapper->add('fooName2', null, ['field_name' => 'fooFilterName2']);

        $this->assertSame(['fooName1', 'fooName2'], $this->datagridMapper->keys());
    }

    public function testReorder(): void
    {
        $this->datagridMapper->add('fooName1', null, ['field_name' => 'fooFilterName1']);
        $this->datagridMapper->add('fooName2', null, ['field_name' => 'fooFilterName2']);
        $this->datagridMapper->add('fooName3', null, ['field_name' => 'fooFilterName3']);
        $this->datagridMapper->add('fooName4', null, ['field_name' => 'fooFilterName4']);

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

    public function testAddOptionRole(): void
    {
        $this->datagridMapper->add('bar', \stdClass::class);

        $this->assertTrue($this->datagridMapper->has('bar'));

        $this->datagridMapper->add('quux', \stdClass::class, [], ['role' => 'ROLE_QUX']);

        $this->assertTrue($this->datagridMapper->has('bar'));
        $this->assertFalse($this->datagridMapper->has('quux'));

        $this->datagridMapper
            ->add('foobar', \stdClass::class, [], ['role' => self::DEFAULT_GRANTED_ROLE])
            ->add('foo', \stdClass::class, [], ['role' => 'ROLE_QUX'])
            ->add('baz', \stdClass::class);

        $this->assertTrue($this->datagridMapper->has('foobar'));
        $this->assertFalse($this->datagridMapper->has('foo'));
        $this->assertTrue($this->datagridMapper->has('baz'));
    }
}
