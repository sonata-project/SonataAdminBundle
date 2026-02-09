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

namespace SensioLabs\AdminBundle\Tests;

use PHPUnit\Framework\TestCase;
use SensioLabs\AdminBundle\SensioLabsAdminBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
final class SensioLabsAdminBundleTest extends TestCase
{
    public function testBuild(): void
    {
        $containerBuilder = $this->createMock(ContainerBuilder::class);

        $containerBuilder->expects(static::exactly(16))
            ->method('addCompilerPass');

        $bundle = new SensioLabsAdminBundle();
        $bundle->build($containerBuilder);
    }
}
