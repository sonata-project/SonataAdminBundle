<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Silas Joisten <silas.joisten@gmail.com>
 */
final class WebpackEntriesCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $configs = $container->getExtensionConfig('webpack_encore');

        $entries = [];
        foreach ($configs as $config) {
            if (!isset($config['builds'])) {
                continue;
            }

            $entries = array_merge($entries, $config['builds']);
        }

        if (!$entries) {
            return;
        }

        $container->getDefinition('twig')
            ->addMethodCall('addGlobal', ['webpack_encore_entries', $entries]);
    }
}
