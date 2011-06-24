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
    protected $configNamespaces = array(
        'templates' => array(
            'layout',
            'ajax'
        )
    );

    protected $requestMatchers = array();

    /**
     *
     * @param array            $configs    An array of configuration settings
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('templates.xml');
        $loader->load('doctrine_orm.xml');
        $loader->load('twig.xml');
        $loader->load('core.xml');
        $loader->load('form_types.xml');
        $loader->load('validator.xml');

        $configuration = new Configuration();
        $processor = new Processor();
        $config = $processor->processConfiguration($configuration, $configs);

        // setups parameters with values in config.yml, default values from external files used if not
        $this->configSetupTemplates($config, $container);

        $container->setAlias('sonata.admin.security.handler', $config['security_handler']);
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

                $container->setParameter(sprintf('sonata.admin.templates.%s', $type), $template);
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
        return 'http://www.sonata-project.org/schema/dic/admin';
    }

    public function getAlias()
    {
        return "sonata_admin";
    }
}