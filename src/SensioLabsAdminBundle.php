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

namespace SensioLabs\AdminBundle;

use SensioLabs\AdminBundle\DependencyInjection\Compiler\AddAuditReadersCompilerPass;
use SensioLabs\AdminBundle\DependencyInjection\Compiler\AddDependencyCallsCompilerPass;
use SensioLabs\AdminBundle\DependencyInjection\Compiler\AddFilterTypeCompilerPass;
use SensioLabs\AdminBundle\DependencyInjection\Compiler\AdminAddInitializeCallCompilerPass;
use SensioLabs\AdminBundle\DependencyInjection\Compiler\AdminMakerCompilerPass;
use SensioLabs\AdminBundle\DependencyInjection\Compiler\AdminSearchCompilerPass;
use SensioLabs\AdminBundle\DependencyInjection\Compiler\ExtensionCompilerPass;
use SensioLabs\AdminBundle\DependencyInjection\Compiler\GlobalVariablesCompilerPass;
use SensioLabs\AdminBundle\DependencyInjection\Compiler\ModelManagerCompilerPass;
use SensioLabs\AdminBundle\DependencyInjection\Compiler\ObjectAclManipulatorCompilerPass;
use SensioLabs\AdminBundle\DependencyInjection\Compiler\TwigStringExtensionCompilerPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class SensioLabsAdminBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new AddDependencyCallsCompilerPass());
        $container->addCompilerPass(new AddFilterTypeCompilerPass());
        $container->addCompilerPass(new AdminSearchCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, -1);
        $container->addCompilerPass(new ExtensionCompilerPass());
        $container->addCompilerPass(new GlobalVariablesCompilerPass());
        $container->addCompilerPass(new ModelManagerCompilerPass());
        $container->addCompilerPass(new ObjectAclManipulatorCompilerPass());
        $container->addCompilerPass(new TwigStringExtensionCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 1);
        $container->addCompilerPass(new AdminMakerCompilerPass());
        $container->addCompilerPass(new AddAuditReadersCompilerPass());
        $container->addCompilerPass(new AdminAddInitializeCallCompilerPass(), PassConfig::TYPE_BEFORE_REMOVING, -100);
    }
}
