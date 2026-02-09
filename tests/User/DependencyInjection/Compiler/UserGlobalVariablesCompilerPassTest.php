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

namespace SensioLabs\AdminBundle\Tests\User\DependencyInjection\Compiler;

use PHPUnit\Framework\TestCase;
use SensioLabs\AdminBundle\User\DependencyInjection\Compiler\UserGlobalVariablesCompilerPass;
use SensioLabs\AdminBundle\User\Twig\UserGlobalVariables;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

final class UserGlobalVariablesCompilerPassTest extends TestCase
{
    public function testDoesNothingWhenGlobalServiceNotDefined(): void
    {
        $container = new ContainerBuilder();
        $pass = new UserGlobalVariablesCompilerPass();

        // Should not throw
        $pass->process($container);

        self::assertTrue(true);
    }

    public function testDoesNothingWhenTwigNotDefined(): void
    {
        $container = new ContainerBuilder();
        $container->setDefinition('sensiolabs.admin.user.twig.global', new Definition(UserGlobalVariables::class));

        $pass = new UserGlobalVariablesCompilerPass();
        $pass->process($container);

        self::assertTrue(true);
    }

    public function testAddsTwigGlobal(): void
    {
        $container = new ContainerBuilder();
        $container->setDefinition('sensiolabs.admin.user.twig.global', new Definition(UserGlobalVariables::class));

        $twigDef = new Definition();
        $container->setDefinition('twig', $twigDef);

        $pass = new UserGlobalVariablesCompilerPass();
        $pass->process($container);

        $calls = $twigDef->getMethodCalls();
        self::assertCount(1, $calls);
        self::assertSame('addGlobal', $calls[0][0]);
        self::assertSame('sensiolabs_user', $calls[0][1][0]);
    }
}
