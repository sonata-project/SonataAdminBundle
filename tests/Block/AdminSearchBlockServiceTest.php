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
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Block\AdminSearchBlockService;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\PagerInterface;
use Sonata\AdminBundle\Filter\FilterInterface;
use Sonata\AdminBundle\Search\SearchHandler;
use Sonata\AdminBundle\Templating\TemplateRegistryInterface;
use Sonata\BlockBundle\Test\BlockServiceTestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Sullivan Senechal <soullivaneuh@gmail.com>
 */
final class AdminSearchBlockServiceTest extends BlockServiceTestCase
{
    /**
     * @var Pool
     */
    private $pool;

    /**
     * @var SearchHandler
     */
    private $searchHandler;

    /**
     * @var TemplateRegistryInterface
     */
    private $templateRegistry;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pool = new Pool(new Container());
        $this->searchHandler = new SearchHandler(true);
        $this->templateRegistry = $this->createMock(TemplateRegistryInterface::class);
        $this->templateRegistry->method('getTemplate')->willReturn('@SonataAdmin/Block/block_search_result.html.twig');
    }

    public function testDefaultSettings(): void
    {
        $blockService = new AdminSearchBlockService(
            $this->twig,
            $this->pool,
            $this->searchHandler,
            $this->templateRegistry,
            'show'
        );
        $blockContext = $this->getBlockContext($blockService);

        $this->assertSettings([
            'admin_code' => '',
            'query' => '',
            'page' => 0,
            'per_page' => 10,
            'icon' => '<i class="fa fa-list"></i>',
        ], $blockContext);
    }

    public function testGlobalSearchReturnsResponse(): void
    {
        $datagrid = $this->createStub(DatagridInterface::class);

        $admin = $this->createMock(AbstractAdmin::class);
        $admin
            ->method('getDatagrid')
            ->willReturn($datagrid);

        $filter = $this->createStub(FilterInterface::class);
        $filter
            ->method('getOption')
            ->with('global_search')
            ->willReturn(true);

        $datagrid
            ->method('getFilters')
            ->willReturn([$filter]);

        $datagrid
            ->method('getPager')
            ->willReturn($this->createStub(PagerInterface::class));

        $blockService = new AdminSearchBlockService(
            $this->twig,
            $this->pool,
            $this->searchHandler,
            $this->templateRegistry,
            'show'
        );
        $blockContext = $this->getBlockContext($blockService);

        $admin->expects(self::once())->method('checkAccess')->with('list');

        $response = $blockService->execute($blockContext);

        static::assertSame('', $response->getContent());
        static::assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testGlobalSearchReturnsEmptyWhenFiltersAreDisabled(): void
    {
        $adminCode = 'code';

        $admin = $this->createMock(AbstractAdmin::class);
        $admin
            ->method('getCode')
            ->willReturn($adminCode);

        $container = new Container();
        $container->set($adminCode, $admin);
        $pool = new Pool($container, [$adminCode]);

        $blockService = new AdminSearchBlockService(
            $this->twig,
            $pool,
            $this->searchHandler,
            $this->templateRegistry,
            'show'
        );
        $blockContext = $this->getBlockContext($blockService);
        $blockContext->setSetting('admin_code', $adminCode);

        $this->searchHandler->configureAdminSearch([$adminCode => false]);
        $admin->expects(self::once())->method('checkAccess')->with('list');

        $this->twig->expects(self::never())->method('render');
        $admin->expects(self::once())->method('checkAccess')->with('list')->willReturn(true);

        $response = $blockService->execute($blockContext);

        static::assertSame('', $response->getContent());
        static::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }
}
