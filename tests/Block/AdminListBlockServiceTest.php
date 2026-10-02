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
use Sonata\BlockBundle\Test\BlockServiceTestCase;
use Symfony\Component\DependencyInjection\Container;

/**
 * @author Sullivan Senechal <soullivaneuh@gmail.com>
 */
final class AdminListBlockServiceTest extends BlockServiceTestCase
{
    private Pool $pool;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pool = new Pool(new Container());
    }

    public function testDefaultSettings(): void
    {
        $blockService = new AdminListBlockService($this->twig, $this->pool, static::createStub(TemplateRegistryInterface::class));
        $blockContext = $this->getBlockContext($blockService);

        self::assertSettings([
            'groups' => false,
        ], $blockContext);
    }
}
