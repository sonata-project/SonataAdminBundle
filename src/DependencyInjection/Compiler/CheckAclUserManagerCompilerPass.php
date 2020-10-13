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

namespace Sonata\AdminBundle\DependencyInjection\Compiler;

use Sonata\AdminBundle\Util\AdminAclUserManagerInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * NEXT_MAJOR: Remove this class.
 *
 * @internal
 */
final class CheckAclUserManagerCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasParameter('sonata.admin.security.acl_user_manager')
            || !$container->hasParameter('sonata.admin.security.fos_user_autoconfigured')) {
            return;
        }

        $userManagerServiceName = $container->getParameter('sonata.admin.security.acl_user_manager');

        if (null === $userManagerServiceName
            || !$container->hasDefinition($userManagerServiceName)
            || $container->getParameter('sonata.admin.security.fos_user_autoconfigured')) {
            return;
        }

        $userManagerDefinition = $container->findDefinition($userManagerServiceName);

        if (!is_a($userManagerDefinition->getClass(), AdminAclUserManagerInterface::class, true)) {
            @trigger_error(sprintf(
                'Configuring the service in sonata_admin.security.acl_user_manager without implementing "%s"'
                .' is deprecated since sonata-project/admin-bundle 3.x and will throw an "%s" exception in 4.0.',
                AdminAclUserManagerInterface::class,
                \InvalidArgumentException::class
            ), E_USER_DEPRECATED);
        }
    }
}
