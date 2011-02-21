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

use Symfony\Component\Config\Definition\Builder;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * This class contains the configuration information for the bundle
 *
 * This information is solely responsible for how the different configuration
 * sections are normalized, and merged.
 *
 * @author Michael Williams <mtotheikle@gmail.com>
 */
class Configuration
{
    /**
     * Generates the configuration tree.
     *
     * @return \Symfony\Component\Config\Definition\NodeInterface
     */
    public function getConfigTree($kernelDebug)
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('sonata_base_application', 'array');

        $rootNode
            ->arrayNode('entities')
                ->isRequired()
                ->useAttributeAsKey('entity_name')
                ->requiresAtLeastOneElement()
                ->prototype('array')
                    ->scalarNode('label')->isRequired()->cannotBeEmpty()->end()
                    ->scalarNode('group')->isRequired()->cannotBeEmpty()->end()
                    ->scalarNode('class')->isRequired()->cannotBeEmpty()->end()
                    ->scalarNode('entity')->isRequired()->cannotBeEmpty()->end()
                    ->scalarNode('controller')->isRequired()->end()
                ->end()
            ->end()
            ->arrayNode('templates')
                ->scalarNode('layout')->cannotBeEmpty()->end()
                ->scalarNode('ajax')->cannotBeEmpty()->end()
            ->end();
            
        return $treeBuilder->buildTree();
    }
}