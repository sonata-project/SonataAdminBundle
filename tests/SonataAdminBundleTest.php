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
use Sonata\AdminBundle\DependencyInjection\Compiler\AddDependencyCallsCompilerPass;
use Sonata\AdminBundle\DependencyInjection\Compiler\AddFilterTypeCompilerPass;
use Sonata\AdminBundle\DependencyInjection\Compiler\ExtensionCompilerPass;
use Sonata\AdminBundle\DependencyInjection\Compiler\GlobalVariablesCompilerPass;
use Sonata\AdminBundle\DependencyInjection\Compiler\ModelManagerCompilerPass;
use Sonata\AdminBundle\DependencyInjection\Compiler\ObjectAclManipulatorCompilerPass;
use Sonata\AdminBundle\DependencyInjection\Compiler\TwigStringExtensionCompilerPass;
use Sonata\AdminBundle\DependencyInjection\Compiler\WebpackEntriesCompilerPass;
use Sonata\AdminBundle\SonataAdminBundle;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class SonataAdminBundleTest extends TestCase
{
    public function testBuild(): void
    {
        $containerBuilder = $this->getMockBuilder(ContainerBuilder::class)
            ->setMethods(['addCompilerPass'])
            ->getMock();

        $containerBuilder->expects($this->exactly(7))
            ->method('addCompilerPass')
            ->willReturnCallback(function (CompilerPassInterface $pass, $type = PassConfig::TYPE_BEFORE_OPTIMIZATION): void {
                if ($pass instanceof AddDependencyCallsCompilerPass) {
                    return;
                }

                if ($pass instanceof AddFilterTypeCompilerPass) {
                    return;
                }

                if ($pass instanceof ExtensionCompilerPass) {
                    return;
                }

                if ($pass instanceof GlobalVariablesCompilerPass) {
                    return;
                }

                if ($pass instanceof ModelManagerCompilerPass) {
                    return;
                }

                if ($pass instanceof ObjectAclManipulatorCompilerPass) {
                    return;
                }

                if ($pass instanceof TwigStringExtensionCompilerPass) {
                    return;
                }

                $this->fail(sprintf(
                    'CompilerPass is not one of the expected types. Expects "%s", "%s", "%s", "%s", "%s", "%s" or "%s", but got "%s".',
                    AddDependencyCallsCompilerPass::class,
                    AddFilterTypeCompilerPass::class,
                    ExtensionCompilerPass::class,
                    GlobalVariablesCompilerPass::class,
                    ModelManagerCompilerPass::class,
                    ObjectAclManipulatorCompilerPass::class,
                    TwigStringExtensionCompilerPass::class,
                    \get_class($pass)
                ));
            });

        $bundle = new SonataAdminBundle();
        $bundle->build($containerBuilder);
    }
}
