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

namespace SensioLabs\AdminBundle\Tests\ORM;

use PHPUnit\Framework\TestCase;
use SensioLabs\AdminBundle\DependencyInjection\ORM\Compiler\AddAuditEntityCompilerPass;
use SensioLabs\AdminBundle\DependencyInjection\ORM\Compiler\AddGuesserCompilerPass;
use SensioLabs\AdminBundle\DependencyInjection\ORM\Compiler\AddTemplatesCompilerPass;
use SensioLabs\AdminBundle\SensioLabsAdminBundle;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
final class SensioLabsAdminBundleTest extends TestCase
{
    public function testBuild(): void
    {
        $containerBuilder = new ContainerBuilder();

        $bundle = new SensioLabsAdminBundle();
        $bundle->build($containerBuilder);

        static::assertNotNull($this->findCompilerPass($containerBuilder, AddGuesserCompilerPass::class));
        static::assertNotNull($this->findCompilerPass($containerBuilder, AddTemplatesCompilerPass::class));
        static::assertNotNull($this->findCompilerPass($containerBuilder, AddAuditEntityCompilerPass::class));
    }

    private function findCompilerPass(ContainerBuilder $container, string $class): ?CompilerPassInterface
    {
        foreach ($container->getCompiler()->getPassConfig()->getPasses() as $pass) {
            if ($pass instanceof $class) {
                return $pass;
            }
        }

        return null;
    }
}
