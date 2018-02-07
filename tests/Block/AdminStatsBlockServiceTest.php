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

use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Block\AdminStatsBlockService;
use Sonata\BlockBundle\Test\AbstractBlockServiceTestCase;

/**
 * @author Sullivan Senechal <soullivaneuh@gmail.com>
 */
class AdminStatsBlockServiceTest extends AbstractBlockServiceTestCase
{
    /**
     * @var Pool
     */
    private $pool;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pool = $this->getMockBuilder(Pool::class)->disableOriginalConstructor()->getMock();
    }

    public function testDefaultSettings(): void
    {
        $blockService = new AdminStatsBlockService('foo', $this->twig, $this->pool);
        $blockContext = $this->getBlockContext($blockService);

        $this->assertSettings([
            'icon' => 'fa-line-chart',
            'text' => 'Statistics',
            'translation_domain' => null,
            'color' => 'bg-aqua',
            'code' => false,
            'filters' => [],
            'limit' => 1000,
            'template' => '@SonataAdmin/Block/block_stats.html.twig',
        ], $blockContext);
    }
}
