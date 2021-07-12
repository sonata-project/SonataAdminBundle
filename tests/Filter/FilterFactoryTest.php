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
use Symfony\Component\DependencyInjection\Container;

final class FilterFactoryTest extends TestCase
{
    public function testUnknownClassType(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No attached service to type named `stdClass`');

        $filter = new FilterFactory(new Container());
        $filter->create('test', \stdClass::class);
    }

    public function testClassType(): void
    {
        $container = new Container();
        $container
            ->set(DefaultType::class, new DefaultType());
        $filter = new FilterFactory($container);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'The service `Sonata\AdminBundle\Form\Type\Filter\DefaultType` must implement `FilterInterface`'
        );

        $filter->create('test', DefaultType::class);
    }

    public function testCreateFilter(): void
    {
        $filter = $this->createMock(FilterInterface::class);
        $filter->expects(self::once())
            ->method('initialize');

        $container = new Container();
        $fqcn = \get_class($filter);
        $container->set($fqcn, $filter);

        $filter = new FilterFactory($container);
        $filter->create('test', $fqcn);
    }
}
