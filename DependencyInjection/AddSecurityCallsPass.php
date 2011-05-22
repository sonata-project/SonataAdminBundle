<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * This code append Admin security roles
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class AddSecurityCallsPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition('sonata_dummy_security');
        $container->removeDefinition('sonata_dummy_security');

        $config = $definition->getArguments();

        $this->createAuthorization($config, $container);
        $this->createRoleHierarchy($config, $container);
    }

    private function createRoleHierarchy($config, ContainerBuilder $container)
    {
        if (!isset($config['role_hierarchy'])) {
            $container->removeDefinition('security.access.role_hierarchy_voter');

            return;
        }

        $parameters = (array) $container->getParameter('security.role_hierarchy.roles');

        $container->setParameter('security.role_hierarchy.roles', array_merge($parameters, $config['role_hierarchy']));
        $container->removeDefinition('security.access.simple_role_voter');
    }

    private function createAuthorization($config, ContainerBuilder $container)
    {
        if (!$config['access_control']) {
            return;
        }

        foreach ($config['access_control'] as $access) {
            $matcher = $this->createRequestMatcher(
                $container,
                $access['path'],
                $access['host'],
                count($access['methods']) === 0 ? null : $access['methods'],
                $access['ip']
            );

            $container->getDefinition('security.access_map')
                      ->addMethodCall('add', array($matcher, $access['roles'], $access['requires_channel']));
        }
    }

    private function createRequestMatcher($container, $path = null, $host = null, $methods = null, $ip = null, array $attributes = array())
    {
        $serialized = serialize(array($path, $host, $methods, $ip, $attributes));
        $id = 'security.request_matcher.'.md5($serialized).sha1($serialized);

        if (isset($this->requestMatchers[$id])) {
            return $this->requestMatchers[$id];
        }

        // only add arguments that are necessary
        $arguments = array($path, $host, $methods, $ip, $attributes);
        while (count($arguments) > 0 && !end($arguments)) {
            array_pop($arguments);
        }

        $container
            ->register($id, '%security.matcher.class%')
            ->setPublic(false)
            ->setArguments($arguments)
        ;

        return $this->requestMatchers[$id] = new Reference($id);
    }

}
