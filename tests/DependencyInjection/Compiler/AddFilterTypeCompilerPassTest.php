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

namespace Sonata\AdminBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\DependencyInjection\Compiler\AddFilterTypeCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class AddFilterTypeCompilerPassTest extends TestCase
{
    private $filterFactory;

    private $fooFilter;

    private $barFilter;

    public function setUp(): void
    {
        $this->filterFactory = $this->createMock(Definition::class);
        $this->fooFilter = $this->createMock(Definition::class);
        $this->barFilter = $this->createMock(Definition::class);
    }

    public function testProcess(): void
    {
        $containerBuilderMock = $this->createMock(ContainerBuilder::class);

        $containerBuilderMock->expects($this->any())
            ->method('getDefinition')
            ->with($this->anything())
            ->willReturnMap([
                ['sonata.admin.builder.filter.factory', $this->filterFactory],
                ['acme.demo.foo_filter', $this->fooFilter],
                ['acme.demo.bar_filter', $this->barFilter],
            ]);

        $containerBuilderMock->expects($this->once())
            ->method('findTaggedServiceIds')
            ->with($this->equalTo('sonata.admin.filter.type'))
            ->willReturn([
                'acme.demo.foo_filter' => [
                    'tag1' => [
                        'alias' => 'foo_filter_alias',
                    ],
                ],
                'acme.demo.bar_filter' => [
                    'tag1' => [
                        'alias' => 'bar_filter_alias',
                    ],
                ],
            ]);

        $this->fooFilter->method('getClass')
            ->willReturn('Acme\Filter\FooFilter');

        $this->barFilter->method('getClass')
            ->willReturn('Acme\Filter\BarFilter');

        $this->filterFactory->expects($this->once())
            ->method('replaceArgument')
            ->with(1, $this->equalTo([
                'foo_filter_alias' => 'acme.demo.foo_filter',
                'Acme\Filter\FooFilter' => 'acme.demo.foo_filter',
                'bar_filter_alias' => 'acme.demo.bar_filter',
                'Acme\Filter\BarFilter' => 'acme.demo.bar_filter',
            ]));

        $extensionsPass = new AddFilterTypeCompilerPass();
        $extensionsPass->process($containerBuilderMock);
    }
}
