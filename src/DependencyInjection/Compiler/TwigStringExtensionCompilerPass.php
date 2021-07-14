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
use Symfony\Component\DependencyInjection\Definition;
use Twig\Extra\String\StringExtension;

/**
 * @internal
 */
final class TwigStringExtensionCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        foreach ($container->findTaggedServiceIds('twig.extension') as $id => $tags) {
            if (StringExtension::class === $container->getDefinition($id)->getClass()) {
                return;
            }
        }

        $definition = new Definition(StringExtension::class);
        $definition->addTag('twig.extension');
        $container->setDefinition(StringExtension::class, $definition);
    }
}
