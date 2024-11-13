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
final class DatagridMapperTest extends TestCase
{
    private const DEFAULT_GRANTED_ROLE = 'ROLE_ADMIN_BAZ';

    /**
     * @var DatagridMapper<object>
     */
    private DatagridMapper $datagridMapper;

    /**
     * @var Datagrid<ProxyQueryInterface<object>>
     */
    private Datagrid $datagrid;

    /**
     * @var AdminInterface<object>&MockObject
     */
    private AdminInterface $admin;

    protected function setUp(): void
    {
        $datagridBuilder = $this->createMock(DatagridBuilderInterface::class);

        /** @var ProxyQueryInterface<object>&MockObject $proxyQuery */
        $proxyQuery = $this->createMock(ProxyQueryInterface::class);
        /** @var PagerInterface<ProxyQueryInterface<object>>&MockObject $pager */
        $pager = $this->createMock(PagerInterface::class);
        $fieldDescriptionCollection = new FieldDescriptionCollection();
        $formBuilder = $this->createMock(FormBuilder::class);

        $this->datagrid = new Datagrid($proxyQuery, $fieldDescriptionCollection, $pager, $formBuilder, []);

        $this->admin = $this->createMock(AdminInterface::class);

        $datagridBuilder
            ->method('addFilter')
            ->willReturnCallback(function (
                Datagrid $datagrid,
                ?string $type,
                FieldDescriptionInterface $fieldDescription,
            ): void {
                $fieldDescription->setType($type);

                $filter = $this->createMock(Filter::class);

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
            ->willReturnCallback(static fn (string $name, ?object $object = null): bool => self::DEFAULT_GRANTED_ROLE === $name);

        $labelTranslatorStrategy = $this->createStub(LabelTranslatorStrategyInterface::class);
        $labelTranslatorStrategy->method('getLabel')->willReturnCallback(
            static fn (string $label, string $context = '', string $type = ''): string => \sprintf('%s.%s_%s', $context, $type, $label)
        );

        $this->admin
            ->method('getLabelTranslatorStrategy')
            ->willReturn($labelTranslatorStrategy);

        $this->datagridMapper = new DatagridMapper($datagridBuilder, $this->datagrid, $this->admin);
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
        static::assertSame([], $filter->getFieldOptions());
        static::assertSame([
            'show_filter' => null,
            'advanced_filter' => true,
            'foo_default_option' => 'bar_default',
            'field_name' => 'fooFilterName',
            'label' => 'fooLabel',
        ], $filter->getOptions());
    }

    public function testGet2(): void
    {
        static::assertFalse($this->datagridMapper->has('fooName'));

        $this->datagridMapper->add('fooName', \stdClass::class, [
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
        static::assertNull($filter->getOption('field_name'));
    }

    public function testAddRemove(): void
    {
        static::assertFalse($this->datagridMapper->has('fooName'));

        $this->datagridMapper->add('fooName', null, ['field_name' => 'fooFilterName']);
        static::assertTrue($this->datagridMapper->has('fooName'));

        $this->datagridMapper->remove('fooName');
        static::assertFalse($this->datagridMapper->has('fooName'));
    }

    public function testAddDuplicateNameException(): void
    {
        $tmpNames = [];
        $this->admin
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
        $this->datagridMapper->add('bar', \stdClass::class);

        static::assertTrue($this->datagridMapper->has('bar'));

        $this->datagridMapper->add('quux', \stdClass::class, [], ['role' => 'ROLE_QUX']);

        static::assertTrue($this->datagridMapper->has('bar'));
        static::assertFalse($this->datagridMapper->has('quux'));

        $this->datagridMapper
            ->add('foobar', \stdClass::class, [], ['role' => self::DEFAULT_GRANTED_ROLE])
            ->add('foo', \stdClass::class, [], ['role' => 'ROLE_QUX'])
            ->add('baz', \stdClass::class);

        static::assertTrue($this->datagridMapper->has('foobar'));
        static::assertFalse($this->datagridMapper->has('foo'));
        static::assertTrue($this->datagridMapper->has('baz'));
    }
}
