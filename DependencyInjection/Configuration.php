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

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This class contains the configuration information for the bundle
 *
 * This information is solely responsible for how the different configuration
 * sections are normalized, and merged.
 *
 * @author Michael Williams <mtotheikle@gmail.com>
 * @author Pablo Díez <pablodip@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('sonata_admin');

        $this->addModelManagersSection($rootNode);
        $this->addTemplatesSection($rootNode);

        return $treeBuilder;
    }

    private function addModelManagersSection($rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('model_managers')
                    ->children()
                        ->booleanNode('doctrine')->end()
                        ->booleanNode('mandango')->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    private function addTemplatesSection($rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('templates')
                    ->children()
                        ->scalarNode('layout')->cannotBeEmpty()->end()
                        ->scalarNode('ajax')->cannotBeEmpty()->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
