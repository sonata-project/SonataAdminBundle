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
use Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity\Post;
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
        $adminFooDefinition->addTag('sonata.admin', [
            'code' => 'admin_foo_code',
            'model_class' => Post::class,
            'global_search' => true,
        ]);
        $this->setDefinition('admin.foo', $adminFooDefinition);

        $adminBarDefinition = new Definition(PostAdmin::class);
        $adminBarDefinition->addTag('sonata.admin', [
            'code' => 'admin_bar_code',
            'model_class' => Post::class,
            'global_search' => false,
        ]);
        $this->setDefinition('admin.bar', $adminBarDefinition);

        $adminBazDefinition = new Definition(PostAdmin::class);
        $adminBazDefinition->addTag('sonata.admin', [
            'code' => 'admin_baz_code',
            'model_class' => Post::class,
            'some_attribute' => 42,
        ]);
        $this->setDefinition('admin.baz', $adminBazDefinition);

        $searchHandlerDefinition = new Definition();
        $this->setDefinition('sonata.admin.search.handler', $searchHandlerDefinition);

        $this->compile();

        self::assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'sonata.admin.search.handler',
            'configureAdminSearch',
            [['admin_foo_code' => true, 'admin_bar_code' => false]]
        );
    }

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new AdminSearchCompilerPass());
    }
}
