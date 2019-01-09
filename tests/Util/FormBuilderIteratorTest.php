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

namespace Sonata\AdminBundle\Tests\Util;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Util\FormBuilderIterator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * @author Mike Meier <mike.meier@ibrows.ch>
 */
class FormBuilderIteratorTest extends TestCase
{
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var FormFactoryInterface
     */
    private $factory;

    /**
     * @var FormBuilder
     */
    private $builder;

    protected function setUp()
    {
        $this->dispatcher = $this->getMockForAbstractClass(EventDispatcherInterface::class);
        $this->factory = $this->getMockForAbstractClass(FormFactoryInterface::class);
        $this->builder = new TestFormBuilder('name', null, $this->dispatcher, $this->factory);
        $this->factory->expects($this->any())->method('createNamedBuilder')->willReturn($this->builder);
    }

    protected function tearDown()
    {
        $this->dispatcher = null;
        $this->factory = null;
        $this->builder = null;
    }

    public function testConstructor()
    {
        new FormBuilderIterator($this->builder);
    }

    public function testGetChildren()
    {
        $this->builder->add('name', 'text');
        $iterator = new FormBuilderIterator($this->builder);
        $this->assertInstanceOf(\get_class($iterator), $iterator->getChildren());
    }

    public function testHasChildren()
    {
        $this->builder->add('name', 'text');
        $iterator = new FormBuilderIterator($this->builder);
        $this->assertTrue($iterator->hasChildren());
    }
}

class TestFormBuilder extends FormBuilder
{
}
