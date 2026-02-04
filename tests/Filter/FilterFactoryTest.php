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

namespace SensioLabs\AdminBundle\Tests\Filter;

use PHPUnit\Framework\TestCase;
use SensioLabs\AdminBundle\Filter\FilterFactory;
use SensioLabs\AdminBundle\Filter\FilterInterface;
use SensioLabs\AdminBundle\Form\Type\Filter\FilterDataType;
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
            ->set(FilterDataType::class, new FilterDataType());
        $filter = new FilterFactory($container);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'The service `SensioLabs\AdminBundle\Form\Type\Filter\FilterDataType` must implement `FilterInterface`'
        );

        $filter->create('test', FilterDataType::class);
    }

    public function testCreateFilter(): void
    {
        $filter = $this->createMock(FilterInterface::class);
        $filter->expects(static::once())
            ->method('initialize');

        $container = new Container();
        $fqcn = $filter::class;
        $container->set($fqcn, $filter);

        $filter = new FilterFactory($container);
        $filter->create('test', $fqcn);
    }
}
