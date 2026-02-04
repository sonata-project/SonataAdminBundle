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

use PHPUnit\Framework\MockObject\MockObject;
use SensioLabs\AdminBundle\Admin\Pool;
use SensioLabs\AdminBundle\Block\AdminListBlockService;
use SensioLabs\AdminBundle\Templating\TemplateRegistryInterface;
use Sonata\BlockBundle\Test\BlockServiceTestCase;
use Symfony\Component\DependencyInjection\Container;

/**
 * @author Sullivan Senechal <soullivaneuh@gmail.com>
 */
final class AdminListBlockServiceTest extends BlockServiceTestCase
{
    private Pool $pool;

    /**
     * @var TemplateRegistryInterface&MockObject
     */
    private TemplateRegistryInterface $templateRegistry;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pool = new Pool(new Container());
        $this->templateRegistry = $this->createMock(TemplateRegistryInterface::class);
    }

    public function testDefaultSettings(): void
    {
        $blockService = new AdminListBlockService($this->twig, $this->pool, $this->templateRegistry);
        $blockContext = $this->getBlockContext($blockService);

        self::assertSettings([
            'groups' => false,
        ], $blockContext);
    }
}
