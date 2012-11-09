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
     * @param ContainerBuilder $container  A ContainerBuilder instance
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
        $loader->load('block.xml');

        $configuration = new Configuration();
        $processor = new Processor();
        $config = $processor->processConfiguration($configuration, $configs);

        $pool = $container->getDefinition('sonata.admin.pool');
        $pool->replaceArgument(1, $config['title']);
        $pool->replaceArgument(2, $config['title_logo']);

        $container->setParameter('sonata.admin.configuration.templates', $config['templates']);
        $container->setParameter('sonata.admin.configuration.admin_services', $config['admin_services']);
        $container->setParameter('sonata.admin.configuration.dashboard_groups', $config['dashboard']['groups']);
        $container->setParameter('sonata.admin.configuration.dashboard_blocks', $config['dashboard']['blocks']);

        $container->setAlias('sonata.admin.security.handler', $config['security']['handler']);

        switch ($config['security']['handler']) {
            case 'sonata.admin.security.handler.role':
                if (count($config['security']['information']) === 0) {
                    $config['security']['information'] = array(
                        'EDIT'      => array('EDIT'),
                        'LIST'      => array('LIST'),
                        'CREATE'    => array('CREATE'),
                        'VIEW'      => array('VIEW'),
                        'DELETE'    => array('DELETE'),
                        'EXPORT'    => array('EXPORT'),
                        'OPERATOR'  => array('OPERATOR'),
                        'MASTER'    => array('MASTER'),
                    );
                }
                break;
            case 'sonata.admin.security.handler.acl':
                if (count($config['security']['information']) === 0) {
                    $config['security']['information'] = array(
                        'GUEST'    => array('VIEW', 'LIST'),
                        'STAFF'    => array('EDIT', 'LIST', 'CREATE'),
                        'EDITOR'   => array('OPERATOR', 'EXPORT'),
                        'ADMIN'    => array('MASTER'),
                    );
                }
                break;
        }

        $container->setParameter('sonata.admin.configuration.security.information', $config['security']['information']);
        $container->setParameter('sonata.admin.configuration.security.admin_permissions', $config['security']['admin_permissions']);
        $container->setParameter('sonata.admin.configuration.security.object_permissions', $config['security']['object_permissions']);

        $loader->load('security.xml');

        /**
         * This is a work in progress, so for now it is hardcoded
         */
        $classes = array(
            'email'    => 'span5',
            'textarea' => 'span5',
            'text'     => 'span5',
            'choice'   => 'span5',
            'integer'  => 'span5',
            'datetime' => 'sonata-medium-date',
            'date'     => 'sonata-medium-date'
        );

        $container->getDefinition('sonata.admin.form.extension.field')
            ->replaceArgument(0, $classes);

        // remove non used service
        if (!isset($bundles['JMSTranslationBundle'])) {
            $container->removeDefinition('sonata.admin.translator.extractor.jms_translator_bundle');
        }

        // set filter persistence
        $container->setParameter('sonata.admin.configuration.filters.persist', $config['persist_filters']);
    }
}