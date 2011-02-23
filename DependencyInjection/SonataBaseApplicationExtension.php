<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\BaseApplicationBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Config\Definition\Processor;

use Symfony\Component\Finder\Finder;

/**
 * SonataBaseApplicationExtension
 *
 * @author      Thomas Rabaix <thomas.rabaix@sonata-project.org>
 * @author      Michael Williams <michael.williams@funsational.com>   
 */
class SonataBaseApplicationExtension extends Extension
{    
	protected $configNamespaces = array(
        'templates' => array(
            'layout',
            'ajax'
        )
    );
    
    /**
     * Parses the configuration to setup the admin controllers and setup routing
     * information. Format is following:
     * 
     * sonata_base_application:
     *    entities:
     *        post:
     *           label:      post
     *           group:      posts
     *           class:      Funsational\SimpleBlogBundle\Admin\Entity\PostAdmin
     *           entity:     Funsational\SimpleBlogBundle\Entity\Post
     *           controller: Funsational\SimpleBlogBundle\Controller\PostAdminController
     *           children:
     *               comment:
     *                  label:      comment
     *                  group:      comments
     *                  class:      Funsational\SimpleBlogBundle\Admin\Entity\CommentAdmin
     *                  entity:     Funsational\SimpleBlogBundle\Entity\Comment
     *                  controller: Funsational\SimpleBlogBundle\Controller\CommentAdminController
     *          options:
     *              show_in_dashboard: true
     *
     * @param array            $config    An array of configuration settings
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('templates.xml');
        
        $configuration = new Configuration();
        $processor = new Processor();
        $config = $processor->process($configuration->getConfigTree($container->getParameter('kernel.debug')), $configs);
        
        // setups parameters with values in config.yml, default values from external files used if not
        $this->configSetupTemplates($config, $container);
        
        // register the twig extension
        $container
            ->register('twig.extension.sonata_base_application', 'Sonata\BaseApplicationBundle\Twig\Extension\SonataBaseApplicationExtension')
            ->addTag('twig.extension');

        // register form builder
        $definition = new Definition('Sonata\BaseApplicationBundle\Builder\FormBuilder', array(new Reference('form.field_factory'), new Reference('form.context'), new Reference('validator')));
        $container->setDefinition('sonata_base_application.builder.orm_form', $definition);

        // register list builder
        $definition = new Definition('Sonata\BaseApplicationBundle\Builder\ListBuilder');
        $container->setDefinition('sonata_base_application.builder.orm_list', $definition);

        // register filter builder
        $definition = new Definition('Sonata\BaseApplicationBundle\Builder\DatagridBuilder');
        $container->setDefinition('sonata_base_application.builder.orm_datagrid', $definition);

        // registers crud action
        $definition = new Definition('Sonata\BaseApplicationBundle\Admin\Pool');
        $definition->addMethodCall('setContainer', array(new Reference('service_container')));
        
        foreach ($config['entities'] as $code => $configuration) {
            $definition->addMethodCall('addConfiguration', array($code, $configuration));
        }

        $container->setDefinition('sonata_base_application.admin.pool', $definition);

        $definition = new Definition('Sonata\BaseApplicationBundle\Route\AdminPoolLoader', array(new Reference('sonata_base_application.admin.pool')));
        $definition->addTag('routing.loader');

        $container->setDefinition('sonata_base_application.route_loader', $definition);
    }
    
    protected function configSetupTemplates($config, $container)
    {
        foreach ($this->configNamespaces as $ns => $params) {

            if (!isset($config[$ns])) {
                continue;
            }

            foreach ($config[$ns] as $type => $template) {
                if (!isset($config[$ns][$type])) {
                    continue;
                }

                $container->setParameter(sprintf('sonata_base_application.templates.%s', $type), $template);
            }
        }
    }

    /**
     * Returns the base path for the XSD files.
     *
     * @return string The XSD base path
     */
    public function getXsdValidationBasePath()
    {
        return __DIR__.'/../Resources/config/schema';
    }

    public function getNamespace()
    {
        return 'http://www.sonata-project.org/schema/dic/base-application';
    }

    public function getAlias()
    {
        return "sonata_base_application";
    }
}