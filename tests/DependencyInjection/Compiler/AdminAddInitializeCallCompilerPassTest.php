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

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\DependencyInjection\Compiler\AdminAddInitializeCallCompilerPass;
use Sonata\AdminBundle\Tests\App\Admin\FooAdmin;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class AdminAddInitializeCallCompilerPassTest extends TestCase
{
    public function testProcess(): void
    {
        $builder = new ContainerBuilder();
        $builder->register('foo', FooAdmin::class)
            ->addTag('sonata.admin');

        (new AdminAddInitializeCallCompilerPass())->process($builder);

        $this->assertSame([['initialize', []]], $builder->getDefinition('foo')->getMethodCalls());
    }
}
