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
use Symfony\Component\DependencyInjection\Container;
use Twig\Environment;

/**
 * @author Sullivan Senechal <soullivaneuh@gmail.com>
 */
class AdminListBlockServiceTest extends BlockServiceTestCase
{
    public function testDefaultSettings(): void
    {
        $blockService = new AdminListBlockService(
            $this->createStub(Environment::class),
            new Pool(new Container()),
            $this->createStub(TemplateRegistryInterface::class)
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
            $this->createStub(Environment::class),
            new Pool(new Container()),
            $this->createStub(TemplateRegistryInterface::class)
        );
        $blockContext = $this->getBlockContext($blockService);

        $this->assertSettings([
            'foo' => 'bar',
            'groups' => true,
        ], $blockContext);
    }
}
