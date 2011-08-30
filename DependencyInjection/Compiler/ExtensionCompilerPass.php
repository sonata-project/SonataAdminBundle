<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class ExtensionCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        foreach ($container->findTaggedServiceIds('sonata.admin.extension') as $id => $attributes) {

            $target = false;
            if (isset($attributes[0]['target'])) {
                $target = $attributes[0]['target'];
            }

            if (!$target || !$container->hasDefinition($target)) {
                continue;
            }

            $container->getDefinition($target)
                ->addMethodCall('addExtension', array(new Reference($id)));
        }
    }
}
