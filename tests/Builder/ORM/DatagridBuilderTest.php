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

namespace SensioLabs\AdminBundle\Tests\Builder\ORM;

use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use SensioLabs\AdminBundle\Admin\AdminInterface;
use SensioLabs\AdminBundle\Datagrid\Datagrid;
use SensioLabs\AdminBundle\Datagrid\DatagridInterface;
use SensioLabs\AdminBundle\Datagrid\Pager;
use SensioLabs\AdminBundle\Datagrid\SimplePager;
use SensioLabs\AdminBundle\FieldDescription\FieldDescriptionCollection;
use SensioLabs\AdminBundle\FieldDescription\TypeGuesserInterface;
use SensioLabs\AdminBundle\Filter\FilterFactoryInterface;
use SensioLabs\AdminBundle\Translator\FormLabelTranslatorStrategy;
use SensioLabs\AdminBundle\Builder\ORM\DatagridBuilder;
use SensioLabs\AdminBundle\Datagrid\ORM\ProxyQueryInterface;
use SensioLabs\AdminBundle\FieldDescription\ORM\FieldDescription;
use SensioLabs\AdminBundle\Filter\ORM\ModelFilter;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Guess\TypeGuess;

/**
 * @author Sullivan Senechal <soullivaneuh@gmail.com>
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
final class DatagridBuilderTest extends TestCase
{
    private DatagridBuilder $datagridBuilder;

    /**
     * @var Stub&TypeGuesserInterface
     */
    private TypeGuesserInterface $typeGuesser;

    /**
     * @var Stub&FormFactoryInterface
     */
    private FormFactoryInterface $formFactory;

    /**
     * @var Stub&FilterFactoryInterface
     */
    private FilterFactoryInterface $filterFactory;

    /**
     * @var MockObject&AdminInterface<object>
     */
    private AdminInterface $admin;

    protected function setUp(): void
    {
        $this->formFactory = static::createStub(FormFactoryInterface::class);
        $this->filterFactory = static::createStub(FilterFactoryInterface::class);
        $this->typeGuesser = static::createStub(TypeGuesserInterface::class);

        $this->datagridBuilder = new DatagridBuilder(
            $this->formFactory,
            $this->filterFactory,
            $this->typeGuesser
        );

        $this->admin = $this->createMock(AdminInterface::class);
        $this->admin->method('getClass')->willReturn('FakeClass');
    }

    /**
     * @phpstan-param class-string $pager
     */
    #[DataProvider('provideGetBaseDatagridCases')]
    public function testGetBaseDatagrid(string $pagerType, string $pager): void
    {
        $proxyQuery = static::createStub(ProxyQueryInterface::class);
        $fieldDescriptionCollection = new FieldDescriptionCollection();
        $formBuilder = static::createStub(FormBuilderInterface::class);

        $this->admin->method('getPagerType')->willReturn($pagerType);
        $this->admin->method('createQuery')->willReturn($proxyQuery);
        $this->admin->method('getList')->willReturn($fieldDescriptionCollection);
        $this->formFactory->method('createNamedBuilder')->willReturn($formBuilder);

        $datagrid = $this->datagridBuilder->getBaseDatagrid($this->admin);

        static::assertInstanceOf(Datagrid::class, $datagrid);
        static::assertInstanceOf($pager, $datagrid->getPager());
    }

    /**
     * @phpstan-return iterable<array-key, array{string, class-string}>
     */
    public static function provideGetBaseDatagridCases(): iterable
    {
        yield 'simple' => [
            Pager::TYPE_SIMPLE,
            SimplePager::class,
        ];
        yield 'default' => [
            Pager::TYPE_DEFAULT,
            Pager::class,
        ];
    }

    public function testGetBaseDatagridBadPagerType(): void
    {
        $this->admin->method('getPagerType')->willReturn('fake');

        $this->expectException(\RuntimeException::class);

        $this->datagridBuilder->getBaseDatagrid($this->admin);
    }

    public function testFixFieldDescription(): void
    {
        $fieldDescription = new FieldDescription('test', [], ['type' => ClassMetadata::ONE_TO_MANY]);
        $fieldDescription->setAdmin($this->admin);

        $this->admin->expects(static::once())->method('attachAdminClass');

        $this->datagridBuilder->fixFieldDescription($fieldDescription);
    }

    public function testFixFieldDescriptionWithoutFieldName(): void
    {
        $fieldDescription = new FieldDescription('test', [], [], [], [], 'fieldName');
        $fieldDescription->setAdmin($this->admin);

        $this->datagridBuilder->fixFieldDescription($fieldDescription);

        static::assertSame('fieldName', $fieldDescription->getOption('field_name'));
    }

    public function testAddFilterNoType(): void
    {
        $datagrid = $this->createMock(DatagridInterface::class);
        $guessType = static::createStub(TypeGuess::class);

        $fieldDescription = new FieldDescription('test');
        $fieldDescription->setAdmin($this->admin);

        $this->admin->expects(static::once())->method('addFilterFieldDescription');
        $this->admin->method('getCode')->willReturn('someFakeCode');
        $this->admin->method('getLabelTranslatorStrategy')->willReturn(new FormLabelTranslatorStrategy());
        $this->typeGuesser->method('guess')->willReturn($guessType);
        $this->filterFactory->method('create')->willReturn(new ModelFilter());

        $guessType->method('getOptions')->willReturn(['name' => 'value']);
        $guessType->method('getType')->willReturn(ModelFilter::class);
        $datagrid->method('addFilter')->with(static::isInstanceOf(ModelFilter::class));

        $this->datagridBuilder->addFilter($datagrid, null, $fieldDescription);
    }
}
