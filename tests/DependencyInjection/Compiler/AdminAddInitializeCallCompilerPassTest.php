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

use PHPUnit\Framework\TestCase;
use SensioLabs\AdminBundle\DependencyInjection\Admin\TaggedAdminInterface;
use SensioLabs\AdminBundle\DependencyInjection\Compiler\AdminAddInitializeCallCompilerPass;
use SensioLabs\AdminBundle\Tests\App\Admin\FooAdmin;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class AdminAddInitializeCallCompilerPassTest extends TestCase
{
    public function testProcess(): void
    {
        $builder = new ContainerBuilder();
        $builder->register('foo', FooAdmin::class)
            ->addTag(TaggedAdminInterface::ADMIN_TAG);

        (new AdminAddInitializeCallCompilerPass())->process($builder);

        static::assertSame([['initialize', []]], $builder->getDefinition('foo')->getMethodCalls());
    }
}
