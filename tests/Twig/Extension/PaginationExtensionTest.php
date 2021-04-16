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

namespace Sonata\AdminBundle\Tests\Twig\Extension;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Twig\Extension\PaginationExtension;

/**
 * NEXT_MAJOR: Remove this class.
 */
final class PaginationExtensionTest extends TestCase
{
    public function testGetPaginationParameters(): void
    {
        $paginationParameters = [
            'filter' => [
                DatagridInterface::PAGE => 1,
            ],
        ];

        $datagrid = $this->getMockBuilder(DatagridInterface::class)
            ->addMethods(['getPaginationParameters'])
            ->getMockForAbstractClass();
        $datagrid
            ->method('getPaginationParameters')
            ->willReturn($paginationParameters);
        $admin = $this->createStub(AdminInterface::class);
        $admin
            ->method('getDatagrid')
            ->willReturn($datagrid);

        $extension = new PaginationExtension();
        $this->assertSame($paginationParameters, $extension->getPaginationParameters($admin, 1));
    }

    /**
     * @group legacy
     */
    public function testGetPaginationParametersFromModelManager(): void
    {
        $paginationParameters = [
            'filter' => [
                DatagridInterface::PAGE => 1,
            ],
        ];

        $datagrid = $this->createStub(DatagridInterface::class);
        $modelManager = $this->createStub(ModelManagerInterface::class);
        $modelManager
            ->method('getPaginationParameters')
            ->willReturn($paginationParameters);

        $admin = $this->createStub(AdminInterface::class);
        $admin
            ->method('getDatagrid')
            ->willReturn($datagrid);

        $admin
            ->method('getModelManager')
            ->willReturn($modelManager);

        $extension = new PaginationExtension();
        $this->assertSame($paginationParameters, $extension->getPaginationParameters($admin, 1));
    }

    public function testGetSortParameters(): void
    {
        $sortParameters = [
            'filter' => [
                DatagridInterface::SORT_ORDER => 'ASC',
                DatagridInterface::SORT_BY => 'name',
            ],
        ];

        $datagrid = $this->getMockBuilder(DatagridInterface::class)
            ->addMethods(['getSortParameters'])
            ->getMockForAbstractClass();
        $datagrid
            ->method('getSortParameters')
            ->willReturn($sortParameters);
        $admin = $this->createStub(AdminInterface::class);
        $admin
            ->method('getDatagrid')
            ->willReturn($datagrid);

        $fieldDescription = $this->createStub(FieldDescriptionInterface::class);

        $extension = new PaginationExtension();
        $this->assertSame($sortParameters, $extension->getSortParameters($fieldDescription, $admin));
    }

    /**
     * @group legacy
     */
    public function testGetSortParametersFromModelManager(): void
    {
        $sortParameters = [
            'filter' => [
                DatagridInterface::SORT_ORDER => 'ASC',
                DatagridInterface::SORT_BY => 'name',
            ],
        ];

        $datagrid = $this->createStub(DatagridInterface::class);
        $modelManager = $this->createStub(ModelManagerInterface::class);
        $modelManager
            ->method('getSortParameters')
            ->willReturn($sortParameters);

        $admin = $this->createStub(AdminInterface::class);
        $admin
            ->method('getDatagrid')
            ->willReturn($datagrid);

        $admin
            ->method('getModelManager')
            ->willReturn($modelManager);

        $fieldDescription = $this->createStub(FieldDescriptionInterface::class);

        $extension = new PaginationExtension();
        $this->assertSame($sortParameters, $extension->getSortParameters($fieldDescription, $admin));
    }
}
