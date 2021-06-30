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
use Sonata\AdminBundle\DependencyInjection\Compiler\AdminSearchCompilerPass;
use Sonata\AdminBundle\Tests\Fixtures\Admin\PostAdmin;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @author Javier Spagnoletti <phansys@gmail.com>
 */
final class AdminSearchCompilerPassTest extends AbstractCompilerPassTestCase
{
    public function testProcess(): void
    {
        $adminFooDefinition = new Definition(PostAdmin::class);
        $adminFooDefinition->addTag('sonata.admin', ['global_search' => true]);
        $this->setDefinition('admin.foo', $adminFooDefinition);

        $adminBarDefinition = new Definition(PostAdmin::class);
        $adminBarDefinition->addTag('sonata.admin', ['global_search' => false]);
        $this->setDefinition('admin.bar', $adminBarDefinition);

        $adminBazDefinition = new Definition(PostAdmin::class);
        $adminBazDefinition->addTag('sonata.admin', ['some_attribute' => 42]);
        $this->setDefinition('admin.baz', $adminBazDefinition);

        $searchHandlerDefinition = new Definition();
        $this->setDefinition('sonata.admin.search.handler', $searchHandlerDefinition);

        $this->compile();

        self::assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'sonata.admin.search.handler',
            'configureAdminSearch',
            [['admin.foo' => true, 'admin.bar' => false]]
        );
    }

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new AdminSearchCompilerPass());
    }
}
