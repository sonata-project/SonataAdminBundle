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
use Sonata\AdminBundle\Search\SearchHandler;
use Sonata\BlockBundle\Test\AbstractBlockServiceTestCase;
use Sonata\BlockBundle\Test\FakeTemplating;

/**
 * @author Sullivan Senechal <soullivaneuh@gmail.com>
 */
class AdminSearchBlockServiceTest extends AbstractBlockServiceTestCase
{
    /**
     * @var Pool
     */
    private $pool;

    /**
     * @var SearchHandler
     */
    private $searchHandler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pool = $this->getMockBuilder(Pool::class)->disableOriginalConstructor()->getMock();
        $this->searchHandler = $this->getMockBuilder(SearchHandler::class)->disableOriginalConstructor()->getMock();
    }

    public function testDefaultSettings(): void
    {
        $blockService = new AdminSearchBlockService('foo', $this->templating, $this->pool, $this->searchHandler);
        $blockContext = $this->getBlockContext($blockService);

        $this->assertSettings([
            'admin_code' => '',
            'query' => '',
            'page' => 0,
            'per_page' => 10,
            'icon' => '<i class="fa fa-list"></i>',
        ], $blockContext);
    }

    public function testGlobalSearchReturnsEmptyWhenFiltersAreDisabled(): void
    {
        $admin = $this->getMockBuilder(AbstractAdmin::class)->disableOriginalConstructor()->getMock();
        $templating = $this->getMockBuilder(FakeTemplating::class)->disableOriginalConstructor()->getMock();

        $blockService = new AdminSearchBlockService('foo', $templating, $this->pool, $this->searchHandler);
        $blockContext = $this->getBlockContext($blockService);

        $this->searchHandler->expects(self::once())->method('search')->willReturn(false);
        $this->pool->expects(self::once())->method('getAdminByAdminCode')->willReturn($admin);
        $admin->expects(self::once())->method('checkAccess')->with('list')->willReturn(true);

        // Make sure the template is never generated (empty response is required,
        // but the FakeTemplate always returns an empty response)
        $templating->expects(self::never())->method('renderResponse');

        $response = $blockService->execute($blockContext);

        static::assertSame('', $response->getContent());
        static::assertSame(204, $response->getStatusCode());
    }
}
