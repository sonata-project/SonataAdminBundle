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
use Sonata\AdminBundle\Block\AdminListBlockService;
use Sonata\AdminBundle\Templating\TemplateRegistryInterface;
use Sonata\AdminBundle\Tests\Fixtures\Block\FakeBlockService;
use Sonata\BlockBundle\Test\BlockServiceTestCase;
use Twig\Environment;

/**
 * @author Sullivan Senechal <soullivaneuh@gmail.com>
 */
class AdminListBlockServiceTest extends BlockServiceTestCase
{
    /**
     * @var Pool
     */
    private $pool;

    /**
     * @var TemplateRegistryInterface
     */
    private $templateRegistry;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pool = $this->createMock(Pool::class);

        $this->templateRegistry = $this->prophesize(TemplateRegistryInterface::class);
    }

    public function testDefaultSettings(): void
    {
        $blockService = new AdminListBlockService(
            $this->createMock(Environment::class),
            $this->pool,
            $this->templateRegistry->reveal()
        );
        $blockContext = $this->getBlockContext($blockService);

        $this->assertSettings([
            'groups' => false,
        ], $blockContext);
    }

    /**
     * @group legacy
     */
    public function testOverriddenDefaultSettings(): void
    {
        $blockService = new FakeBlockService(
            $this->createMock(Environment::class),
            $this->pool,
            $this->templateRegistry->reveal()
        );
        $blockContext = $this->getBlockContext($blockService);

        $this->assertSettings([
            'foo' => 'bar',
            'groups' => true,
        ], $blockContext);
    }
}
