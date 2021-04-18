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

namespace Sonata\AdminBundle\Tests\Block;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Block\AdminPreviewBlockService;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionCollection;
use Sonata\BlockBundle\Test\BlockServiceTestCase;
use Symfony\Component\DependencyInjection\Container;
use Twig\Environment;

/**
 * @author Javier Spagnoletti <phansys@gmail.com>
 */
final class AdminPreviewBlockServiceTest extends BlockServiceTestCase
{
    /**
     * @var Pool
     */
    private $pool;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pool = new Pool(new Container());
    }

    public function testDefaultSettings(): void
    {
        $blockService = new AdminPreviewBlockService($this->createStub(Environment::class), $this->pool);
        $blockContext = $this->getBlockContext($blockService);

        $this->assertSettings([
            'text' => 'Preview',
            'filters' => [],
            'icon' => false,
            'limit' => 10,
            'code' => false,
            'template' => '@SonataAdmin/Block/block_admin_preview.html.twig',
            'remove_list_fields' => [ListMapper::NAME_ACTIONS],
        ], $blockContext);
    }

    public function testBlockExecution(): void
    {
        $adminCode = 'admin.bar';
        $responseContent = '<div>AdminBlockPreview</div>';

        $admin = $this->createMock(AdminInterface::class);
        $admin
            ->method('getCode')
            ->willReturn($adminCode);

        $container = new Container();
        $container->set($adminCode, $admin);
        $pool = new Pool($container, [$adminCode]);
        $datagrid = $this->createStub(DatagridInterface::class);
        $twig = $this->createMock(Environment::class);

        $blockService = new AdminPreviewBlockService($twig, $pool);
        $blockContext = $this->getBlockContext($blockService)->setSetting('code', 'admin.bar');

        $admin->expects(self::once())->method('checkAccess')->with('list')->willReturn(true);
        $admin->expects(self::exactly(2))->method('getDatagrid')->willReturn($datagrid);
        $admin->expects(self::once())->method('getList')->willReturn(new FieldDescriptionCollection());
        $twig->expects(self::once())->method('render')->willReturn($responseContent);

        $response = $blockService->execute($blockContext);

        static::assertSame($responseContent, $response->getContent());
        static::assertSame(200, $response->getStatusCode());
    }
}
