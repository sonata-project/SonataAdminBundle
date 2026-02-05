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

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use SensioLabs\AdminBundle\DependencyInjection\Compiler\AddFilterTypeCompilerPass;
use SensioLabs\AdminBundle\Filter\FilterFactoryInterface;
use SensioLabs\AdminBundle\Tests\Fixtures\Filter\BarFilter;
use SensioLabs\AdminBundle\Tests\Fixtures\Filter\FooFilter;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;

final class AddFilterTypeCompilerPassTest extends AbstractCompilerPassTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $filterFactoryDefinition = new Definition(FilterFactoryInterface::class, [
            null,
        ]);

        $this->container
            ->setDefinition('sensiolabs.admin.builder.filter.factory', $filterFactoryDefinition);
    }

    public function testProcess(): void
    {
        $fooFilter = new Definition(FooFilter::class);
        $fooFilter
            ->addTag('sensiolabs.admin.filter.type', [
                'alias' => 'foo_filter_alias',
            ]);

        $this->container
            ->setDefinition('acme.demo.foo_filter', $fooFilter);

        $barFilter = new Definition(BarFilter::class);
        $barFilter
            ->addTag('sensiolabs.admin.filter.type');

        $this->container
            ->setDefinition('acme.demo.bar_filter', $barFilter);

        $this->compile();

        $serviceLocator = $this->container->getDefinition('sensiolabs.admin.builder.filter.factory')->getArgument(0);
        static::assertInstanceOf(Reference::class, $serviceLocator);

        self::assertContainerBuilderHasServiceLocator(
            (string) $serviceLocator,
            [
                FooFilter::class => 'acme.demo.foo_filter',
                BarFilter::class => 'acme.demo.bar_filter',
            ]
        );
    }

    public function testServicesMustHaveAClassName(): void
    {
        $filter = new Definition('not_existing_class');
        $filter
            ->addTag('sensiolabs.admin.filter.type');

        $this->container
            ->setDefinition('acme.demo.foo_filter', $filter);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Class "not_existing_class" used for service "acme.demo.foo_filter" cannot be found.');

        $this->compile();
    }

    public function testServicesMustImplementFilterInterface(): void
    {
        $filter = new Definition(\stdClass::class);
        $filter
            ->addTag('sensiolabs.admin.filter.type');

        $this->container
            ->setDefinition('acme.demo.foo_filter', $filter);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Service "acme.demo.foo_filter" MUST implement interface "SensioLabs\AdminBundle\Filter\FilterInterface".');

        $this->compile();
    }

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new AddFilterTypeCompilerPass());
    }
}
