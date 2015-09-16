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
use Sonata\AdminBundle\Block\AdminListBlockService;
use Sonata\BlockBundle\Tests\Block\AbstractBlockServiceTest;

/**
 * @author Sullivan Senechal <soullivaneuh@gmail.com>
 */
class AdminListBlockServiceTest extends AbstractBlockServiceTest
{
    /**
     * @var Pool
     */
    private $pool;

    protected function setUp()
    {
        parent::setUp();

        $this->pool = $this->getMockBuilder('Sonata\AdminBundle\Admin\Pool')->disableOriginalConstructor()->getMock();
    }

    public function testDefaultSettings()
    {
        $blockService = new AdminListBlockService('foo', $this->templating, $this->pool);
        $blockContext = $this->getBlockContext($blockService);

        $this->assertSettings(array(
            'groups' => false,
        ), $blockContext);
    }

    public function testOverriddenDefaultSettings()
    {
        $blockService = new FakeBlockService('foo', $this->templating, $this->pool);
        $blockContext = $this->getBlockContext($blockService);

        $this->assertSettings(array(
            'foo'    => 'bar',
            'groups' => true,
        ), $blockContext);
    }
}
