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
use Sonata\AdminBundle\DependencyInjection\Compiler\RoleSecurityCompilerPass;
use Sonata\AdminBundle\Security\Handler\RoleSecurityHandler;
use Sonata\AdminBundle\Tests\App\Admin\FooAdmin;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class RoleSecurityCompilerPassTest extends AbstractCompilerPassTestCase
{
    public function testCustomRolePrefixTagging(): void
    {
        $roleSecurityHandlerDefinition = new Definition(RoleSecurityHandler::class);
        $this->container->setDefinition('sonata.admin.security.handler.role', $roleSecurityHandlerDefinition);

        $definition = new Definition(FooAdmin::class);
        $definition->addTag('sonata.admin.role_security', [
            'role_prefix' => 'ROLE_BAZ',
        ]);
        $this->container->setDefinition('admin.foo', $definition);
        $this->compile();

        self::assertContainerBuilderHasServiceDefinitionWithTag('admin.foo', 'sonata.admin.role_security', [
            'role_prefix' => 'ROLE_BAZ',
        ]);

        self::assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'sonata.admin.security.handler.role',
            'setCustomRolePrefix',
            ['admin.foo', 'ROLE_BAZ']
        );
    }

    public function testRepeatedCustomRolePrefixTagging(): void
    {
        $roleSecurityHandlerDefinition = new Definition(RoleSecurityHandler::class);
        $this->container->setDefinition('sonata.admin.security.handler.role', $roleSecurityHandlerDefinition);

        $definition = new Definition(FooAdmin::class);
        $definition->addTag('sonata.admin.role_security', [
            'role_prefix' => 'ROLE_BAZ',
        ]);
        $definition->addTag('sonata.admin.role_security', [
            'role_prefix' => 'ROLE_BAR',
        ]);
        $this->container->setDefinition('admin.foo', $definition);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'Unable to set role prefix for admin.foo to "ROLE_BAR", because
                it has already been assigned with role prefix "ROLE_BAZ".'
        );

        $this->compile();
    }

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new RoleSecurityCompilerPass());
    }
}
