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
use Sonata\BlockBundle\Test\BlockServiceTestCase;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

/**
 * NEXT_MAJOR: Remove this class.
 *
 * @group legacy
 *
 * @author Sullivan Senechal <soullivaneuh@gmail.com>
 */
class DeprecatedAdminSearchBlockServiceTest extends BlockServiceTestCase
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

        $this->pool = $this->createMock(Pool::class);
        $this->searchHandler = $this->createMock(SearchHandler::class);
    }

    /**
     * @expectedDeprecation Passing null as argument 2 to Sonata\AdminBundle\Block\AdminSearchBlockService::__construct() is deprecated since sonata-project/admin-bundle 3.x and will throw a \TypeError in version 4.0. You must pass an instance of Sonata\AdminBundle\Admin\Pool instead.
     */
    public function testDefaultSettings(): void
    {
        $blockService = new AdminSearchBlockService(
            $this->createMock(Environment::class),
            null,
            $this->pool,
            $this->searchHandler
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

    /**
     * @expectedDeprecation Passing null as argument 2 to Sonata\AdminBundle\Block\AdminSearchBlockService::__construct() is deprecated since sonata-project/admin-bundle 3.x and will throw a \TypeError in version 4.0. You must pass an instance of Sonata\AdminBundle\Admin\Pool instead.
     */
    public function testGlobalSearchReturnsEmptyWhenFiltersAreDisabled(): void
    {
        $admin = $this->createMock(AbstractAdmin::class);

        $blockService = new AdminSearchBlockService(
            $this->createMock(Environment::class),
            null,
            $this->pool,
            $this->searchHandler
        );
        $blockContext = $this->getBlockContext($blockService);

        $this->searchHandler->expects(self::once())->method('search')->willReturn(false);
        $this->pool->expects(self::once())->method('getAdminByAdminCode')->willReturn($admin);
        $admin->expects(self::once())->method('checkAccess')->with('list')->willReturn(true);

        $response = $blockService->execute($blockContext);

        static::assertSame('', $response->getContent());
        static::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }
}
