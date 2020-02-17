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

        if (0 === count($entries)) {
            return;
        }

        $container->getDefinition('twig')
            ->addMethodCall('addGlobal', ['sonata_admin_webpack_entries', $entries]);
    }
}
