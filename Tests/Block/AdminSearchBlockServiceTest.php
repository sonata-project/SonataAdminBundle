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
use Sonata\AdminBundle\Block\AdminSearchBlockService;
use Sonata\AdminBundle\Search\SearchHandler;
use Sonata\BlockBundle\Tests\Block\AbstractBlockServiceTest;

/**
 * @author Sullivan Senechal <soullivaneuh@gmail.com>
 */
class AdminSearchBlockServiceTest extends AbstractBlockServiceTest
{
    /**
     * @var Pool
     */
    private $pool;

    /**
     * @var SearchHandler
     */
    private $searchHandler;

    protected function setUp()
    {
        parent::setUp();

        $this->pool = $this->getMockBuilder('Sonata\AdminBundle\Admin\Pool')->disableOriginalConstructor()->getMock();
        $this->searchHandler = $this->getMockBuilder('Sonata\AdminBundle\Search\SearchHandler')->disableOriginalConstructor()->getMock();
    }

    public function testDefaultSettings()
    {
        $blockService = new AdminSearchBlockService('foo', $this->templating, $this->pool, $this->searchHandler);
        $blockContext = $this->getBlockContext($blockService);

        $this->assertSettings(array(
            'admin_code' => false,
            'query'      => '',
            'page'       => 0,
            'per_page'   => 10,
            'icon'       => '<i class="fa fa-list"></i>',
        ), $blockContext);
    }
}
