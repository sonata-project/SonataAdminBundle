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
use Sonata\AdminBundle\Datagrid\PagerInterface;
use Sonata\AdminBundle\Search\SearchHandler;
use Sonata\AdminBundle\Templating\TemplateRegistryInterface;
use Sonata\BlockBundle\Test\BlockServiceTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Sullivan Senechal <soullivaneuh@gmail.com>
 */
class AdminSearchBlockServiceTest extends BlockServiceTestCase
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

    /**
     * @var string
     */
    private $emptyBoxesOption;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pool = $this->createMock(Pool::class);
        $this->searchHandler = $this->createMock(SearchHandler::class);
        $this->templateRegistry = $this->createMock(TemplateRegistryInterface::class);
        $this->templateRegistry->method('getTemplate')->willReturn('@SonataAdmin/Block/block_search_result.html.twig');
        $this->emptyBoxesOption = 'show';
    }

    public function testDefaultSettings(): void
    {
        $blockService = new AdminSearchBlockService($this->twig, $this->pool, $this->searchHandler, $this->templateRegistry, $this->emptyBoxesOption);
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
        $admin = $this->createMock(AbstractAdmin::class);
        $pagerInterface = $this->createMock(PagerInterface::class);

        $blockService = new AdminSearchBlockService($this->twig, $this->pool, $this->searchHandler, $this->templateRegistry, $this->emptyBoxesOption);
        $blockContext = $this->getBlockContext($blockService);

        $this->searchHandler->expects(self::once())->method('search')->willReturn($pagerInterface);
        $this->pool->expects(self::once())->method('getAdminByAdminCode')->willReturn($admin);
        $admin->expects(self::once())->method('checkAccess')->with('list');

        $response = $blockService->execute($blockContext);

        static::assertSame('', $response->getContent());
        static::assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testGlobalSearchReturnsEmptyWhenFiltersAreDisabled(): void
    {
        $admin = $this->createMock(AbstractAdmin::class);

        $blockService = new AdminSearchBlockService($this->twig, $this->pool, $this->searchHandler, $this->templateRegistry, $this->emptyBoxesOption);
        $blockContext = $this->getBlockContext($blockService);

        $this->searchHandler->expects(self::once())->method('search')->willReturn(null);
        $this->pool->expects(self::once())->method('getAdminByAdminCode')->willReturn($admin);
        $admin->expects(self::once())->method('checkAccess')->with('list');

        $this->twig->expects(self::never())->method('render');

        $response = $blockService->execute($blockContext);

        static::assertSame('', $response->getContent());
        static::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }
}
