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

use Symfony\Component\Finder\Finder;

/**
 * SonataAdminBundleExtension
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 * @author Michael Williams <michael.williams@funsational.com>
 * @author Pablo Díez <pablodip@gmail.com>
 */
class SonataAdminExtension extends Extension
{
    protected $configNamespaces = array(
        'templates' => array(
            'layout',
            'ajax'
        )
    );

    /**
     *
     * @param array            $config    An array of configuration settings
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('config.xml');

        $processor = new Processor();
        $configuration = new Configuration();
        $config = $processor->processConfiguration($configuration, $configs);

        if (!empty($config['model_managers']['doctrine'])) {
            $loader->load('model_manager_doctrine.xml');
        }

        if (!empty($config['model_managers']['mandango'])) {
            $loader->load('model_manager_mandango.xml');
        }

        // setups parameters with values in config.yml, default values from external files used if not
        $this->configSetupTemplates($config, $container);
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

                $container->setParameter(sprintf('sonata_admin.templates.%s', $type), $template);
            }
        }
    }
}
