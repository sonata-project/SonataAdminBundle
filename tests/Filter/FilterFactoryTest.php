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
use Sonata\AdminBundle\Tests\Fixtures\Filter\FooFilter;
use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;
use Symfony\Component\DependencyInjection\Container;

class FilterFactoryTest extends TestCase
{
    use ExpectDeprecationTrait;

    public function testEmptyType(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The type must be defined');

        $filter = new FilterFactory(new Container());
        $filter->create('test', null);
    }

    public function testUnknownType(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No attached service to type named `mytype`');

        $filter = new FilterFactory(new Container());
        $filter->create('test', 'mytype');
    }

    public function testUnknownClassType(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No attached service to type named `Sonata\AdminBundle\Form\Type\Filter\FooType`');

        $filter = new FilterFactory(new Container());
        $filter->create('test', 'Sonata\AdminBundle\Form\Type\Filter\FooType');
    }

    public function testClassType(): void
    {
        $container = new Container();
        $container
            ->set(DefaultType::class, new DefaultType());
        $filter = new FilterFactory($container, [
            DefaultType::class => DefaultType::class,
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'The service `Sonata\AdminBundle\Form\Type\Filter\DefaultType` must implement `FilterInterface`'
        );

        $filter->create('test', DefaultType::class);
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testItTriggersDeprecationWithoutTheService(): void
    {
        $filter = new FilterFactory(new Container());

        $this->expectDeprecation('Not declaring a filter as service is deprecated since sonata-project/admin-bundle 3.x and will not work in 4.0. You MUST register a service with class name (Sonata\AdminBundle\Tests\Fixtures\Filter\FooFilter) instead.');

        $filter->create('test', FooFilter::class);
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testInvalidTypeInstance(): void
    {
        $container = new Container();
        $container->set('mytype', new \stdClass());

        $filter = new FilterFactory($container, ['mytype' => 'mytype']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The service `mytype` must implement `FilterInterface`');

        $filter->create('test', 'mytype');
    }

    public function testCreateFilter(): void
    {
        $filter = $this->createMock(FilterInterface::class);
        $filter->expects($this->once())
            ->method('initialize');

        $container = new Container();
        $container->set('my.filter.id', $filter);

        $fqcn = \get_class($filter);

        $filter = new FilterFactory($container, [$fqcn => 'my.filter.id']);
        $filter->create('test', $fqcn);
    }

    /**
     * @group legacy
     */
    public function testCreateFilterWithTypeName(): void
    {
        $filter = $this->createMock(FilterInterface::class);
        $filter->expects($this->once())
            ->method('initialize');

        $container = new Container();
        $container->set('mytype', $filter);

        $filter = new FilterFactory($container, ['mytype' => 'mytype']);
        $filter->create('test', 'mytype');
    }
}
