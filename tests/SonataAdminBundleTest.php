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
use Sonata\AdminBundle\DependencyInjection\Compiler\AddAuditReadersCompilerPass;
use Sonata\AdminBundle\DependencyInjection\Compiler\AddDependencyCallsCompilerPass;
use Sonata\AdminBundle\DependencyInjection\Compiler\AddFilterTypeCompilerPass;
use Sonata\AdminBundle\DependencyInjection\Compiler\AdminAddInitializeCallCompilerPass;
use Sonata\AdminBundle\DependencyInjection\Compiler\AdminMakerCompilerPass;
use Sonata\AdminBundle\DependencyInjection\Compiler\AdminSearchCompilerPass;
use Sonata\AdminBundle\DependencyInjection\Compiler\ExtensionCompilerPass;
use Sonata\AdminBundle\DependencyInjection\Compiler\GlobalVariablesCompilerPass;
use Sonata\AdminBundle\DependencyInjection\Compiler\ModelManagerCompilerPass;
use Sonata\AdminBundle\DependencyInjection\Compiler\ObjectAclManipulatorCompilerPass;
use Sonata\AdminBundle\DependencyInjection\Compiler\TwigStringExtensionCompilerPass;
use Sonata\AdminBundle\SonataAdminBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class SonataAdminBundleTest extends TestCase
{
    public function testBuild(): void
    {
        $containerBuilder = $this->createMock(ContainerBuilder::class);

        $containerBuilder->expects(self::exactly(11))
            ->method('addCompilerPass')
            ->withConsecutive(
                [new AddDependencyCallsCompilerPass()],
                [new AddFilterTypeCompilerPass()],
                [new AdminSearchCompilerPass()],
                [new ExtensionCompilerPass()],
                [new GlobalVariablesCompilerPass()],
                [new ModelManagerCompilerPass()],
                [new ObjectAclManipulatorCompilerPass()],
                [new TwigStringExtensionCompilerPass()],
                [new AdminMakerCompilerPass()],
                [new AddAuditReadersCompilerPass()],
                [new AdminAddInitializeCallCompilerPass()],
            );

        $bundle = new SonataAdminBundle();
        $bundle->build($containerBuilder);
    }
}
