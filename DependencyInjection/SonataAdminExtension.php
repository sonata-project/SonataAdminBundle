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

use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Config\Definition\Processor;


/**
 * SonataAdminBundleExtension
 *
 * @author      Thomas Rabaix <thomas.rabaix@sonata-project.org>
 * @author      Michael Williams <michael.williams@funsational.com>
 */
class SonataAdminExtension extends Extension
{
    /**
     *
     * @param array            $configs    An array of configuration settings
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');
        if (isset($bundles['SonataUserBundle'])) {
            // integrate the SonataUserBundle / FOSUserBundle if the bundle exists
            array_unshift($configs, array(
                'templates' => array(
                    'user_block' => 'SonataUserBundle:Admin/Core:user_block.html.twig'
                )
            ));
        }

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('templates.xml');
        $loader->load('twig.xml');
        $loader->load('core.xml');
        $loader->load('form_types.xml');
        $loader->load('validator.xml');
        $loader->load('route.xml');

        $configuration = new Configuration();
        $processor = new Processor();
        $config = $processor->processConfiguration($configuration, $configs);

        $pool = $container->getDefinition('sonata.admin.pool');
        $pool->addMethodCall('setTemplates', array($config['templates']));
        $pool->replaceArgument(1, $config['title']);
        $pool->replaceArgument(2, $config['title_logo']);
        $pool->addMethodCall('__hack__', $config);

        $container->setAlias('sonata.admin.security.handler', $config['security_handler']);

        /**
         * This is a work in progress, so for now it is hardcoded
         */
        $classes = array(
            'textarea' => 'sonata-medium',
            'text'     => 'sonata-medium',
            'choice'   => 'sonata-medium',
            'integer'  => 'sonata-medium',
            'datetime' => 'sonata-medium-date'
        );

        $container->getDefinition('sonata.admin.form.extension.field')
            ->replaceArgument(0, $classes);
    }

    public function getNamespace()
    {
        return 'http://www.sonata-project.org/schema/dic/admin';
    }
}