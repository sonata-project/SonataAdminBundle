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

namespace Sonata\AdminBundle\Tests\Filter;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Filter\FilterFactory;
use Sonata\AdminBundle\Filter\FilterInterface;
use Sonata\AdminBundle\Form\Type\Filter\DefaultType;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FilterFactoryTest extends TestCase
{
    public function testEmptyType(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The type must be defined');

        $container = $this->getMockForAbstractClass(ContainerInterface::class);

        $filter = new FilterFactory($container, []);
        $filter->create('test', null);
    }

    public function testUnknownType(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No attached service to type named `mytype`');

        $container = $this->getMockForAbstractClass(ContainerInterface::class);

        $filter = new FilterFactory($container, []);
        $filter->create('test', 'mytype');
    }

    public function testUnknownClassType(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No attached service to type named `Sonata\AdminBundle\Form\Type\Filter\FooType`');

        $container = $this->getMockForAbstractClass(ContainerInterface::class);

        $filter = new FilterFactory($container, []);
        $filter->create('test', 'Sonata\AdminBundle\Form\Type\Filter\FooType');
    }

    public function testClassType(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'The service `Sonata\AdminBundle\Form\Type\Filter\DefaultType` must implement `FilterInterface`'
        );

        $container = $this->getMockForAbstractClass(ContainerInterface::class);

        $filter = new FilterFactory($container, []);
        $filter->create('test', DefaultType::class);
    }

    public function testInvalidTypeInstance(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The service `mytype` must implement `FilterInterface`');

        $container = $this->getMockForAbstractClass(ContainerInterface::class);
        $container->expects($this->once())
            ->method('get')
            ->willReturn(false);

        $filter = new FilterFactory($container, ['mytype' => 'mytype']);
        $filter->create('test', 'mytype');
    }

    public function testCreateFilter(): void
    {
        $filter = $this->getMockForAbstractClass(FilterInterface::class);
        $filter->expects($this->once())
            ->method('initialize');

        $container = $this->getMockForAbstractClass(ContainerInterface::class);
        $container->expects($this->once())
            ->method('get')
            ->willReturn($filter);

        $filter = new FilterFactory($container, ['mytype' => 'mytype']);
        $filter->create('test', 'mytype');
    }
}
