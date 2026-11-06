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
     * @var DatagridMapper
     */
    private $datagridMapper;

    /**
     * @var Datagrid
     */
    private $datagrid;

    protected function setUp(): void
    {
        $datagridBuilder = $this->createMock(DatagridBuilderInterface::class);

        $proxyQuery = $this->createMock(ProxyQueryInterface::class);
        $pager = $this->createMock(PagerInterface::class);
        $fieldDescriptionCollection = $this->createMock(FieldDescriptionCollection::class);
        $formBuilder = $this->getMockBuilder(FormBuilder::class)
                     ->disableOriginalConstructor()
                     ->getMock();

        $this->datagrid = new Datagrid($proxyQuery, $fieldDescriptionCollection, $pager, $formBuilder, []);

        // NEXT_MAJOR: Change to $this->createStub(AdminInterface::class).
        $admin = $this->getMockBuilder(AdminInterface::class)
            ->addMethods(['createFieldDescription'])
            ->getMockForAbstractClass();

        $datagridBuilder
            ->method('addFilter')
            ->willReturnCallback(function (
                Datagrid $datagrid,
                ?string $type,
                FieldDescriptionInterface $fieldDescription,
                AdminInterface $admin
            ): void {
                $fieldDescription->setType($type);

                $filter = $this->getMockForAbstractClass(Filter::class);

                $filter
                    ->method('getDefaultOptions')
                    ->willReturn(['foo_default_option' => 'bar_default']);

                $filter->initialize($fieldDescription->getName(), $fieldDescription->getOptions());
                $datagrid->addFilter($filter);
            });

        $admin
            ->method('createFieldDescription')
            ->willReturnCallback(function (string $name, array $options = []): FieldDescriptionInterface {
                $fieldDescription = $this->getMockForAbstractClass(BaseFieldDescription::class, [$name, []]);
                $fieldDescription->setOptions($options);

                return $fieldDescription;
            });

        $admin
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

        $admin
            ->method('getLabelTranslatorStrategy')
            ->willReturn($labelTranslatorStrategy);

        $this->datagridMapper = new DatagridMapper($datagridBuilder, $this->datagrid, $admin);
    }

    public function testFluidInterface(): void
    {
        static::assertSame($this->datagridMapper, $this->datagridMapper->add('fooName', null, ['field_name' => 'fooFilterName']));
        static::assertSame($this->datagridMapper, $this->datagridMapper->remove('fooName'));
        static::assertSame($this->datagridMapper, $this->datagridMapper->reorder([]));
    }

    public function testGet(): void
    {
        static::assertFalse($this->datagridMapper->has('fooName'));

        $this->datagridMapper->add('foo.name', null, [
            'field_name' => 'fooFilterName',
            'label' => 'fooLabel',
        ]);

        $filter = $this->datagridMapper->get('foo.name');
        static::assertInstanceOf(FilterInterface::class, $filter);
        static::assertSame('foo.name', $filter->getName());
        static::assertSame('foo__name', $filter->getFormName());
        static::assertSame(TextType::class, $filter->getFieldType());
        static::assertSame('fooLabel', $filter->getLabel());
        static::assertSame(['required' => false], $filter->getFieldOptions());
        static::assertSame([
            'show_filter' => null,
            'advanced_filter' => true,
            'foo_default_option' => 'bar_default',
            'field_name' => 'fooFilterName',
            'label' => 'fooLabel',
            'placeholder' => 'short_object_description_placeholder',
            'link_parameters' => [],
        ], $filter->getOptions());
    }

    public function testGet2(): void
    {
        static::assertFalse($this->datagridMapper->has('fooName'));

        $this->datagridMapper->add('fooName', 'foo_type', [
            'label' => 'fooLabel',
            'field_name' => 'fooFilterName',
            'field_type' => 'foo_field_type',
            'field_options' => ['foo_field_option' => 'baz'],
            'foo_filter_option' => 'foo_filter_option_value',
            'foo_default_option' => 'bar_custom',
        ]);

        $filter = $this->datagridMapper->get('fooName');
        static::assertInstanceOf(FilterInterface::class, $filter);
        static::assertSame('fooName', $filter->getName());
        static::assertSame('fooName', $filter->getFormName());
        static::assertSame('foo_field_type', $filter->getFieldType());
        static::assertSame('fooLabel', $filter->getLabel());
        static::assertSame(['foo_field_option' => 'baz'], $filter->getFieldOptions());
        static::assertSame([
            'show_filter' => null,
            'advanced_filter' => true,
            'foo_default_option' => 'bar_custom',
            'label' => 'fooLabel',
            'field_name' => 'fooFilterName',
            'field_type' => 'foo_field_type',
            'field_options' => ['foo_field_option' => 'baz'],
            'foo_filter_option' => 'foo_filter_option_value',
            'placeholder' => 'short_object_description_placeholder',
            'link_parameters' => [],
        ], $filter->getOptions());
    }

    public function testAdd(): void
    {
        $this->datagridMapper->add('fooName');

        static::assertTrue($this->datagridMapper->has('fooName'));

        $filter = $this->datagridMapper->get('fooName');

        static::assertInstanceOf(FilterInterface::class, $filter);
        static::assertSame('fooName', $filter->getName());
        static::assertSame('filter.label_fooName', $filter->getLabel());
    }

    public function testAddWithoutFieldName(): void
    {
        $this->datagridMapper->add('foo.bar');

        static::assertTrue($this->datagridMapper->has('foo.bar'));

        $filter = $this->datagridMapper->get('foo.bar');

        static::assertInstanceOf(FilterInterface::class, $filter);
        static::assertSame('foo.bar', $filter->getName());
        static::assertSame('bar', $filter->getOption('field_name'));
    }

    public function testAddRemove(): void
    {
        static::assertFalse($this->datagridMapper->has('fooName'));

        $this->datagridMapper->add('fooName', null, ['field_name' => 'fooFilterName']);
        static::assertTrue($this->datagridMapper->has('fooName'));

        $this->datagridMapper->remove('fooName');
        static::assertFalse($this->datagridMapper->has('fooName'));
    }

    public function testAddException(): void
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage(
            'Unknown field name in datagrid mapper. Field name should be either of FieldDescriptionInterface interface or string'
        );

        $this->datagridMapper->add(12345);
    }

    public function testAddDuplicateNameException(): void
    {
        $tmpNames = [];
        $this->datagridMapper->getAdmin()
            ->expects(static::exactly(2))
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

        static::assertSame(['fooName1', 'fooName2'], $this->datagridMapper->keys());
    }

    public function testReorder(): void
    {
        $this->datagridMapper->add('fooName1', null, ['field_name' => 'fooFilterName1']);
        $this->datagridMapper->add('fooName2', null, ['field_name' => 'fooFilterName2']);
        $this->datagridMapper->add('fooName3', null, ['field_name' => 'fooFilterName3']);
        $this->datagridMapper->add('fooName4', null, ['field_name' => 'fooFilterName4']);

        static::assertSame([
            'fooName1',
            'fooName2',
            'fooName3',
            'fooName4',
        ], array_keys($this->datagrid->getFilters()));

        $this->datagridMapper->reorder(['fooName3', 'fooName2', 'fooName1', 'fooName4']);

        static::assertSame([
            'fooName3',
            'fooName2',
            'fooName1',
            'fooName4',
        ], array_keys($this->datagrid->getFilters()));
    }

    public function testAddOptionRole(): void
    {
        $this->datagridMapper->add('bar', 'bar');

        static::assertTrue($this->datagridMapper->has('bar'));

        $this->datagridMapper->add('quux', 'bar', [], ['role' => 'ROLE_QUX']);

        static::assertTrue($this->datagridMapper->has('bar'));
        static::assertFalse($this->datagridMapper->has('quux'));

        $this->datagridMapper
            ->add('foobar', 'bar', [], ['role' => self::DEFAULT_GRANTED_ROLE])
            ->add('foo', 'bar', [], ['role' => 'ROLE_QUX'])
            ->add('baz', 'bar');

        static::assertTrue($this->datagridMapper->has('foobar'));
        static::assertFalse($this->datagridMapper->has('foo'));
        static::assertTrue($this->datagridMapper->has('baz'));
    }
}
