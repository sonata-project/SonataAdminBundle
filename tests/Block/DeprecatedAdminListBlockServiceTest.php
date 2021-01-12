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
 * NEXT_MAJOR: Remove this class.
 *
 * @group legacy
 *
 * @author Sullivan Senechal <soullivaneuh@gmail.com>
 */
class DeprecatedAdminListBlockServiceTest extends BlockServiceTestCase
{
    /**
     * @expectedDeprecation Passing null as argument 2 to Sonata\AdminBundle\Block\AdminListBlockService::__construct() is deprecated since sonata-project/admin-bundle 3.76 and will throw a \TypeError in version 4.0. You must pass an instance of Sonata\AdminBundle\Admin\Pool instead.
     */
    public function testDefaultSettings(): void
    {
        $blockService = new AdminListBlockService(
            $this->createStub(Environment::class),
            null,
            $this->createStub(Pool::class),
            $this->createStub(TemplateRegistryInterface::class)
        );
        $blockContext = $this->getBlockContext($blockService);

        $this->assertSettings([
            'groups' => false,
        ], $blockContext);
    }

    /**
     * @expectedDeprecation Passing null as argument 2 to Sonata\AdminBundle\Block\AdminListBlockService::__construct() is deprecated since sonata-project/admin-bundle 3.76 and will throw a \TypeError in version 4.0. You must pass an instance of Sonata\AdminBundle\Admin\Pool instead.
     */
    public function testOverriddenDefaultSettings(): void
    {
        $blockService = new FakeBlockService(
            $this->createStub(Environment::class),
            null,
            $this->createStub(Pool::class),
            $this->createStub(TemplateRegistryInterface::class)
        );
        $blockContext = $this->getBlockContext($blockService);

        $this->assertSettings([
            'foo' => 'bar',
            'groups' => true,
        ], $blockContext);
    }
}
