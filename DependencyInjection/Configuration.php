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
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This class contains the configuration information for the bundle
 *
 * This information is solely responsible for how the different configuration
 * sections are normalized, and merged.
 *
 * @author Michael Williams <mtotheikle@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('sonata_admin', 'array');

        $this->addTemplateSection($rootNode);

        return $treeBuilder;
    }

    private function addTemplateSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->scalarNode('security_handler')->defaultValue('sonata.admin.security.handler.noop')->end()
                ->arrayNode('templates')
                    ->children()
                        ->scalarNode('layout')->cannotBeEmpty()->end()
                        ->scalarNode('ajax')->cannotBeEmpty()->end()
                    ->end()
                ->end()
            ->end()
        ->end();
    }
}
