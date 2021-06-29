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

namespace Sonata\AdminBundle\Tests\DependencyInjection\Compiler;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Sonata\AdminBundle\DependencyInjection\Compiler\TwigStringExtensionCompilerPass;
use Symfony\Bundle\TwigBundle\DependencyInjection\Compiler\TwigEnvironmentPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Twig\Extra\String\StringExtension;

class TwigStringExtensionCompilerPassTest extends AbstractCompilerPassTestCase
{
    public function testLoadTwigStringExtension(): void
    {
        $this->compile();

        self::assertContainerBuilderHasServiceDefinitionWithTag(StringExtension::class, 'twig.extension');
    }

    public function testLoadTwigStringExtensionWithExtraBundle(): void
    {
        $definition = new Definition(StringExtension::class);
        $definition->addTag('twig.extension');
        $this->container->setDefinition('twig.extension.string', $definition);
        $this->compile();

        self::assertContainerBuilderHasServiceDefinitionWithTag('twig.extension.string', 'twig.extension');
        self::assertContainerBuilderNotHasService(StringExtension::class);
    }

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new TwigEnvironmentPass());
        $container->addCompilerPass(new TwigStringExtensionCompilerPass());
    }
}
