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
use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

// NEXT_MAJOR: Uncomment next line.
//use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

final class AddFilterTypeCompilerPassTest extends AbstractCompilerPassTestCase
{
    use ExpectDeprecationTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $filterFactoryDefinition = new Definition(FilterFactoryInterface::class, [
            null,
            [],
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

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'sonata.admin.builder.filter.factory',
            1,
            [
                'foo_filter_alias' => 'acme.demo.foo_filter',
                FooFilter::class => 'acme.demo.foo_filter',
                BarFilter::class => 'acme.demo.bar_filter',
            ]
        );
    }

    /**
     * NEXT_MAJOR: Remove legacy group.
     *
     * @group legacy
     */
    public function testServicesMustHaveAClassName(): void
    {
        $filter = new Definition('not_existing_class');
        $filter
            ->addTag('sonata.admin.filter.type');

        $this->container
            ->setDefinition('acme.demo.foo_filter', $filter);

        // NEXT_MAJOR: Remove deprecation and uncomment exception.
        $this->expectDeprecation('Not declaring a filter with an existing class name is deprecated since sonata-project/admin-bundle 3.x and will not work in 4.0. You MUST register a service with an existing class name for service "acme.demo.foo_filter".');
//        $this->expectException(InvalidArgumentException::class);
//        $this->expectExceptionMessage('Class "not_existing_class" used for service "acme.demo.foo_filter" cannot be found.');

        $this->compile();
    }

    /**
     * NEXT_MAJOR: Remove legacy group.
     *
     * @group legacy
     */
    public function testServicesMustImplementFilterInterface(): void
    {
        $filter = new Definition(\stdClass::class);
        $filter
            ->addTag('sonata.admin.filter.type');

        $this->container
            ->setDefinition('acme.demo.foo_filter', $filter);

        // NEXT_MAJOR: Remove deprecation and uncomment exception.
        $this->expectDeprecation('Registering service "acme.demo.foo_filter" without implementing interface "Sonata\AdminBundle\Filter\FilterInterface" is deprecated since sonata-project/admin-bundle 3.x and will be mandatory in 4.0.');
//        $this->expectException(InvalidArgumentException::class);
//        $this->expectExceptionMessage('Service "acme.demo.foo_filter" MUST implement interface "Sonata\AdminBundle\Filter\FilterInterface".');

        $this->compile();
    }

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new AddFilterTypeCompilerPass());
    }
}
