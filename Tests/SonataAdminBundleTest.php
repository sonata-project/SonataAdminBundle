<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests;

use Sonata\AdminBundle\SonataAdminBundle;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Sonata\AdminBundle\DependencyInjection\Compiler\AddDependencyCallsCompilerPass;
use Sonata\AdminBundle\DependencyInjection\Compiler\AddFilterTypeCompilerPass;
use Sonata\AdminBundle\DependencyInjection\Compiler\ExtensionCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Test for SonataAdminBundle
 *
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class SonataAdminBundleTest extends \PHPUnit_Framework_TestCase
{
    public function testBuild()
    {
        $containerBuilder = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');

        $containerBuilder->expects($this->exactly(3))
            ->method('addCompilerPass')
            ->will($this->returnCallback(function (CompilerPassInterface $pass, $type = PassConfig::TYPE_BEFORE_OPTIMIZATION) {
                if ($pass instanceof AddDependencyCallsCompilerPass) {
                    return;
                }

                if ($pass instanceof AddFilterTypeCompilerPass) {
                    return;
                }

                if ($pass instanceof ExtensionCompilerPass) {
                    return;
                }

                $this->fail(sprintf('Compiler pass is not one of the expected types. Expects "Sonata\AdminBundle\DependencyInjection\Compiler\AddDependencyCallsCompilerPass", "Sonata\AdminBundle\DependencyInjection\Compiler\AddFilterTypeCompilerPass" or "Sonata\AdminBundle\DependencyInjection\Compiler\ExtensionCompilerPass", but got "%s".', get_class($pass)));
            }));

        $bundle = new SonataAdminBundle();
        $bundle->build($containerBuilder);
    }
}
