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
use Sonata\AdminBundle\DependencyInjection\Compiler\AddFilterTypeCompilerPass;
use Sonata\AdminBundle\Filter\FilterFactoryInterface;
use Sonata\AdminBundle\Tests\Fixtures\Filter\BarFilter;
use Sonata\AdminBundle\Tests\Fixtures\Filter\FooFilter;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

final class AddFilterTypeCompilerPassTest extends AbstractCompilerPassTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $filterFactoryDefinition = new Definition(FilterFactoryInterface::class, [
            null,
        ]);

        $this->container
            ->setDefinition('sonata.admin.builder.filter.factory', $filterFactoryDefinition);
    }

    public function testProcess(): void
    {
        $fooFilter = new Definition(FooFilter::class);
        $fooFilter
            ->addTag('sonata.admin.filter.type', [
                'alias' => 'foo_filter_alias',
            ]);

        $this->container
            ->setDefinition('acme.demo.foo_filter', $fooFilter);

        $barFilter = new Definition(BarFilter::class);
        $barFilter
            ->addTag('sonata.admin.filter.type');

        $this->container
            ->setDefinition('acme.demo.bar_filter', $barFilter);

        $this->compile();

        self::assertContainerBuilderHasServiceLocator(
            (string) $this->container->getDefinition('sonata.admin.builder.filter.factory')->getArgument(0),
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
            ->addTag('sonata.admin.filter.type');

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
            ->addTag('sonata.admin.filter.type');

        $this->container
            ->setDefinition('acme.demo.foo_filter', $filter);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Service "acme.demo.foo_filter" MUST implement interface "Sonata\AdminBundle\Filter\FilterInterface".');

        $this->compile();
    }

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new AddFilterTypeCompilerPass());
    }
}
