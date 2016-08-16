<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\DependencyInjection;

use Sonata\AdminBundle\DependencyInjection\Compiler\AddFilterTypeCompilerPass;

class AddFilterTypeCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    private $filterFactory;

    private $fooFilter;

    private $barFilter;

    public function setUp()
    {
        $this->filterFactory = $this->getMock('Symfony\Component\DependencyInjection\Definition');
        $this->fooFilter = $this->getMock('Symfony\Component\DependencyInjection\Definition');
        $this->barFilter = $this->getMock('Symfony\Component\DependencyInjection\Definition');
    }

    public function testProcess()
    {
        $containerBuilderMock = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');

        $containerBuilderMock->expects($this->any())
            ->method('getDefinition')
            ->with($this->anything())
            ->will($this->returnValueMap(array(
                array('sonata.admin.builder.filter.factory', $this->filterFactory),
                array('acme.demo.foo_filter', $this->fooFilter),
                array('acme.demo.bar_filter', $this->barFilter),
            )));

        $containerBuilderMock->expects($this->once())
            ->method('findTaggedServiceIds')
            ->with($this->equalTo('sonata.admin.filter.type'))
            ->will($this->returnValue(array(
                'acme.demo.foo_filter' => array(
                    'tag1' => array(
                        'alias' => 'foo_filter_alias',
                    ),
                ),
                'acme.demo.bar_filter' => array(
                    'tag1' => array(
                        'alias' => 'bar_filter_alias',
                    ),
                ),
            )));

        $this->fooFilter->method('getClass')
            ->will($this->returnValue('Acme\Filter\FooFilter'));

        $this->barFilter->method('getClass')
            ->will($this->returnValue('Acme\Filter\BarFilter'));

        $this->filterFactory->expects($this->once())
            ->method('replaceArgument')
            ->with(1, $this->equalTo(array(
                'foo_filter_alias' => 'acme.demo.foo_filter',
                'Acme\Filter\FooFilter' => 'acme.demo.foo_filter',
                'bar_filter_alias' => 'acme.demo.bar_filter',
                'Acme\Filter\BarFilter' => 'acme.demo.bar_filter',
            )));

        $extensionsPass = new AddFilterTypeCompilerPass();
        $extensionsPass->process($containerBuilderMock);
    }
}
