<?php

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
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @author Sullivan Senechal <soullivaneuh@gmail.com>
 */
class AdminStatsBlockServiceTest extends AbstractBlockServiceTestCase
{
    /**
     * @var Pool
     */
    private $pool;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    protected function setUp()
    {
        parent::setUp();

        $this->pool = $this->getMockBuilder('Sonata\AdminBundle\Admin\Pool')->disableOriginalConstructor()->getMock();
        $this->translator = $this->getMockBuilder('Symfony\Component\Translation\TranslatorInterface')->disableOriginalConstructor()->getMock();
    }

    public function testDefaultSettings()
    {
        $blockService = new AdminStatsBlockService('foo', $this->templating, $this->pool, $this->translator);
        $blockContext = $this->getBlockContext($blockService);

        $this->assertSettings(array(
            'icon' => 'fa fa-line-chart',
            'text' => 'Statistics',
            'color' => 'bg-aqua',
            'code' => false,
            'class' => '',
            'filters' => array(),
            'limit' => 1000,
            'template' => 'SonataAdminBundle:Block:block_stats_simple.html.twig',
        ), $blockContext);
    }
}
