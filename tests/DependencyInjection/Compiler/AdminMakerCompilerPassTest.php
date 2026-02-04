<?php

declare(strict_types=1);

/*
 * This file is part of sensiolabs-de/admin-bundle.
 *
 * (c) SensioLabs Deutschland <info@sensiolabs.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\AdminBundle\Tests\DependencyInjection\Compiler;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use SensioLabs\AdminBundle\Controller\CRUDController;
use SensioLabs\AdminBundle\DependencyInjection\Compiler\AdminMakerCompilerPass;
use SensioLabs\AdminBundle\Maker\AdminMaker;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

final class AdminMakerCompilerPassTest extends AbstractCompilerPassTestCase
{
    public function testDoesNothingWithoutAdminMaker(): void
    {
        $this->compile();

        self::assertContainerBuilderNotHasService('sonata.admin.maker');
    }

    public function testDoesNothingWithoutDefaultControllerParameter(): void
    {
        $definition = new Definition(AdminMaker::class);
        $definition->setArguments([
            'dir',
            [],
            CRUDController::class,
        ]);
        $this->container->setDefinition('sonata.admin.maker', $definition);

        $this->compile();

        self::assertContainerBuilderHasServiceDefinitionWithArgument(
            'sonata.admin.maker',
            2,
            CRUDController::class
        );
    }

    public function testDoesNothingWithoutDefaultControllerNotBeingAService(): void
    {
        $definition = new Definition(AdminMaker::class);
        $definition->setArguments([
            'dir',
            [],
            CRUDController::class,
        ]);
        $this->container->setDefinition('sonata.admin.maker', $definition);

        $this->container->setParameter('sonata.admin.configuration.default_controller', CRUDController::class);

        $this->compile();

        self::assertContainerBuilderHasServiceDefinitionWithArgument(
            'sonata.admin.maker',
            2,
            CRUDController::class
        );
    }

    public function testReplacesTheServiceArgumentWithClassName(): void
    {
        $definition = new Definition(AdminMaker::class);
        $definition->setArguments([
            'dir',
            [],
            'sonata.admin.controller.crud',
        ]);
        $this->container->setDefinition('sonata.admin.maker', $definition);

        $definition = new Definition(CRUDController::class);
        $this->container->setDefinition('sonata.admin.controller.crud', $definition);

        $this->container->setParameter('sonata.admin.configuration.default_controller', 'sonata.admin.controller.crud');

        $this->compile();

        self::assertContainerBuilderHasServiceDefinitionWithArgument(
            'sonata.admin.maker',
            2,
            CRUDController::class
        );
    }

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new AdminMakerCompilerPass());
    }
}
