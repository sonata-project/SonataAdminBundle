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
use Sonata\AdminBundle\Block\AdminPreviewBlockService;
use Sonata\BlockBundle\Test\AbstractBlockServiceTestCase;

/**
 * @author Javier Spagnoletti <phansys@gmail.com>
 */
class AdminPreviewBlockServiceTest extends AbstractBlockServiceTestCase
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
        $blockService = new AdminPreviewBlockService('bar', $this->templating, $this->pool);
        $blockContext = $this->getBlockContext($blockService);

        $this->assertSettings([
            'text' => 'Preview',
            'filters' => [],
            'limit' => 10,
            'code' => false,
            'template' => '@SonataAdmin/Block/block_admin_preview.html.twig',
            'remove_list_fields' => ['_action'],
        ], $blockContext);
    }
}
