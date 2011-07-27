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
 * Add all dependencies to the Admin class, this avoid to write to many lines
 * in the configuration files.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class AddFormExtensionCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $ignores = array('form', 'csrf');

        foreach ($container->findTaggedServiceIds('form.type') as $id => $attributes) {
            $name = isset($attributes[0]['alias']) ? $attributes[0]['alias'] : false;

            if (!$name || in_array($name, $ignores)) {
                continue;
            }

            $definition = new Definition('Sonata\AdminBundle\Form\Extension\Field\Type\FormTypeFieldExtension', array($name));
            $definition->addTag('form.type_extension', array('alias' => $name));

            $container->setDefinition(sprintf('sonata.admin.form.extension.%s', $name), $definition);
        }

        // append the extension
        $typeExtensions = array();

        foreach ($container->findTaggedServiceIds('form.type_extension') as $serviceId => $tag) {
            $alias = isset($tag[0]['alias'])
                ? $tag[0]['alias']
                : $serviceId;

            $typeExtensions[$alias][] = $serviceId;
        }

        $container->getDefinition('form.extension')->replaceArgument(2, $typeExtensions);
    }
}
