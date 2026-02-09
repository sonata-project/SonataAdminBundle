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
use SensioLabs\AdminBundle\DependencyInjection\Compiler\DashboardControllerCompilerPass;
use SensioLabs\AdminBundle\DependencyInjection\Compiler\ExtensionCompilerPass;
use SensioLabs\AdminBundle\DependencyInjection\Compiler\GlobalVariablesCompilerPass;
use SensioLabs\AdminBundle\DependencyInjection\Compiler\ModelManagerCompilerPass;
use SensioLabs\AdminBundle\DependencyInjection\Compiler\ObjectAclManipulatorCompilerPass;
use SensioLabs\AdminBundle\DependencyInjection\Compiler\TwigStringExtensionCompilerPass;
use SensioLabs\AdminBundle\DependencyInjection\ORM\Compiler\AddAuditEntityCompilerPass;
use SensioLabs\AdminBundle\DependencyInjection\ORM\Compiler\AddGuesserCompilerPass;
use SensioLabs\AdminBundle\DependencyInjection\ORM\Compiler\AddTemplatesCompilerPass;
use SensioLabs\AdminBundle\DependencyInjection\SensioLabsAdminExtension;
use SensioLabs\AdminBundle\User\DependencyInjection\Compiler\RolesMatrixCompilerPass;
use SensioLabs\AdminBundle\User\DependencyInjection\Compiler\UserGlobalVariablesCompilerPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class SensioLabsAdminBundle extends Bundle
{
    public function getContainerExtension(): ExtensionInterface
    {
        return new SensioLabsAdminExtension();
    }

    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new AddDependencyCallsCompilerPass());
        $container->addCompilerPass(new AddFilterTypeCompilerPass());
        $container->addCompilerPass(new ExtensionCompilerPass());
        $container->addCompilerPass(new GlobalVariablesCompilerPass());
        $container->addCompilerPass(new ModelManagerCompilerPass());
        $container->addCompilerPass(new ObjectAclManipulatorCompilerPass());
        $container->addCompilerPass(new TwigStringExtensionCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 1);
        $container->addCompilerPass(new AdminMakerCompilerPass());
        $container->addCompilerPass(new AddAuditReadersCompilerPass());
        $container->addCompilerPass(new AdminAddInitializeCallCompilerPass(), PassConfig::TYPE_BEFORE_REMOVING, -100);
        $container->addCompilerPass(new DashboardControllerCompilerPass());

        // ORM compiler passes
        $container->addCompilerPass(new AddGuesserCompilerPass());
        $container->addCompilerPass(new AddTemplatesCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, -1);
        $container->addCompilerPass(new AddAuditEntityCompilerPass());

        // User management compiler passes
        $container->addCompilerPass(new UserGlobalVariablesCompilerPass());
        $container->addCompilerPass(new RolesMatrixCompilerPass());
    }
}
