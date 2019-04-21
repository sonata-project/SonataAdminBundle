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

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\FieldDescriptionCollection;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Block\AdminPreviewBlockService;
use Sonata\AdminBundle\Datagrid\Datagrid;
use Sonata\BlockBundle\Test\AbstractBlockServiceTestCase;
use Sonata\BlockBundle\Test\FakeTemplating;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Javier Spagnoletti <phansys@gmail.com>
 */
class AdminPreviewBlockServiceTest extends AbstractBlockServiceTestCase
{
    /**
     * @var Pool
     */
    private $pool;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pool = $this->createMock(Pool::class);
    }

    public function testDefaultSettings(): void
    {
        $blockService = new AdminPreviewBlockService('bar', $this->templating, $this->pool);
        $blockContext = $this->getBlockContext($blockService);

        $this->assertSettings([
            'text' => 'Preview',
            'filters' => [],
            'limit' => 10,
            'code' => false,
            'template' => '@SonataAdmin/Block/block_admin_preview.html.twig',
            'remove_list_fields' => ['_action'],
        ], $blockContext);
    }

    public function testBlockExecution(): void
    {
        $admin = $this->createMock(AbstractAdmin::class);
        $templating = $this->createMock(FakeTemplating::class);
        $datagrid = $this->createMock(Datagrid::class);

        $blockService = new AdminPreviewBlockService('bar', $templating, $this->pool);
        $blockContext = $this->getBlockContext($blockService)->setSetting('code', 'admin.bar');

        $this->pool->expects(self::once())->method('getAdminByAdminCode')->willReturn($admin);
        $admin->expects(self::once())->method('checkAccess')->with('list')->willReturn(true);
        $admin->expects(self::exactly(2))->method('getDatagrid')->willReturn($datagrid);
        $admin->expects(self::once())->method('getList')->willReturn(new FieldDescriptionCollection());
        $templating->expects(self::once())->method('renderResponse')->willReturn(new Response());

        $response = $blockService->execute($blockContext);

        static::assertSame('', $response->getContent());
        static::assertSame(200, $response->getStatusCode());
    }
}
