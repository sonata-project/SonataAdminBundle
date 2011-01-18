<?php
/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Bundle\Sonata\BaseApplicationBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;

use Symfony\Component\Finder\Finder;

/**
 * BaseApplicationExtension
 *
 *
 * @author     Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class BaseApplicationExtension extends Extension
{
    protected $configNamespaces = array(
        'templates' => array(
            'layout',
            'ajax'
        )
    );
    
    /**
     * Loads the url shortener configuration.
     *
     * @param array            $config    An array of configuration settings
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    public function configLoad($config, ContainerBuilder $container)
    {

        // loads config from external files
        $this->configLoadFiles($container);
        
        // setups parameters with values in config.yml, default values from external files used if not
        $this->configSetup($config, $container);

        // register the twig extension
        $container
            ->register('twig.extension.base_application', 'Bundle\Sonata\BaseApplicationBundle\Twig\Extension\BaseApplicationExtension')
            ->addTag('twig.extension');

        // registers crud action
        $definition = new Definition('Bundle\Sonata\BaseApplicationBundle\Admin\Pool');
        $definition->addMethodCall('setContainer', array(new Reference('service_container')));
        foreach ($config['entities'] as $code => $configuration) {
            if (!isset($configuration['group'])) {
                $configuration['group'] = 'default';
            }

            if (!isset($configuration['label'])) {
                $configuration['label'] = $code;
            }
            
            $definition->addMethodCall('addConfiguration', array($code, $configuration));
        }

        $container->setDefinition('base_application.admin.pool', $definition);

        $definition = new Definition('Bundle\Sonata\BaseApplicationBundle\Route\AdminPoolLoader', array(new Reference('base_application.admin.pool')));
        $definition->addTag('routing.loader');

        $container->setDefinition('base_application.route_loader', $definition);

    }
    
    protected function configLoadFiles($container)
    {
        $loader = new XmlFileLoader($container, __DIR__ . '/../Resources/config');

        foreach ($this->configNamespaces as $ns => $params) {
            $loader->load(sprintf('%s.xml', $ns));
        }
    }
    
    protected function configSetup($config, $container)
    {
        foreach ($this->configNamespaces as $ns => $params) {

            if (!isset($config[$ns])) {
                continue;
            }

            foreach ($config[$ns] as $type => $template) {
                if (!isset($config[$ns][$type])) {
                    continue;
                }

                $container->setParameter(sprintf('base_application.templates.%s', $type), $template);
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

        return "base_application";
    }
}