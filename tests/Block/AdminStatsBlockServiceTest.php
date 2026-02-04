<?php

declare(strict_types=1);

/*
 * This file is part of sensiolabs-de/admin-bundle.
 *
 * (c) SensioLabs Deutschland <info@sensiolabs.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\AdminBundle\Tests\Block;

use SensioLabs\AdminBundle\Admin\Pool;
use SensioLabs\AdminBundle\Block\AdminStatsBlockService;
use Sonata\BlockBundle\Test\BlockServiceTestCase;
use Symfony\Component\DependencyInjection\Container;

/**
 * @author Sullivan Senechal <soullivaneuh@gmail.com>
 */
final class AdminStatsBlockServiceTest extends BlockServiceTestCase
{
    private Pool $pool;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pool = new Pool(new Container());
    }

    public function testDefaultSettings(): void
    {
        $blockService = new AdminStatsBlockService($this->twig, $this->pool);
        $blockContext = $this->getBlockContext($blockService);

        self::assertSettings([
            'icon' => 'fas fa-chart-line',
            'text' => 'Statistics',
            'translation_domain' => null,
            'color' => 'bg-aqua',
            'code' => false,
            'filters' => [],
            'limit' => 1000,
            'template' => '@SensioLabsAdmin/Block/block_stats.html.twig',
        ], $blockContext);
    }
}
