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
use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

/**
 * @author Sullivan Senechal <soullivaneuh@gmail.com>
 */
final class AdminSearchBlockServiceTest extends BlockServiceTestCase
{
    use ExpectDeprecationTrait;

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

        $this->pool = new Pool(new Container());
        $this->searchHandler = new SearchHandler(true);
    }

    public function testDefaultSettings(): void
    {
        $blockService = new AdminSearchBlockService(
            $this->createMock(Environment::class),
            $this->pool,
            $this->searchHandler,
            'show'
        );
        $blockContext = $this->getBlockContext($blockService);

        $this->assertSettings([
            'admin_code' => '',
            'query' => '',
            'page' => 0,
            'per_page' => 10,
            'icon' => 'fa fa-list',
        ], $blockContext);
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testDefaultSettingsWithoutEmptyBoxOption(): void
    {
        $this->expectDeprecation('Not passing a string as argument 4 to %s() is deprecated since sonata-project/admin-bundle 3.81 and will throw a \TypeError in version 4.0.');

        $blockService = new AdminSearchBlockService(
            $this->createMock(Environment::class),
            $this->pool,
            $this->searchHandler,
        );
        $blockContext = $this->getBlockContext($blockService);

        $this->assertSettings([
            'admin_code' => '',
            'query' => '',
            'page' => 0,
            'per_page' => 10,
            'icon' => 'fa fa-list',
        ], $blockContext);
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
            $this->createStub(Environment::class),
            $pool,
            $this->searchHandler,
            'show'
        );
        $blockContext = $this->getBlockContext($blockService);
        $blockContext->setSetting('admin_code', $adminCode);

        $this->searchHandler->configureAdminSearch([$adminCode => false]);
        $admin->expects(self::once())->method('checkAccess')->with('list')->willReturn(true);

        $response = $blockService->execute($blockContext);

        static::assertSame('', $response->getContent());
        static::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }
}
