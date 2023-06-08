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

namespace Sonata\AdminBundle\Tests;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\SonataAdminBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
final class SonataAdminBundleTest extends TestCase
{
    public function testBuild(): void
    {
        $containerBuilder = $this->createMock(ContainerBuilder::class);

        $containerBuilder->expects(static::exactly(11))
            ->method('addCompilerPass');

        $bundle = new SonataAdminBundle();
        $bundle->build($containerBuilder);
    }
}
