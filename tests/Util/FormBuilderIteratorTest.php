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
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * @author Mike Meier <mike.meier@ibrows.ch>
 */
final class FormBuilderIteratorTest extends TestCase
{
    private EventDispatcherInterface $dispatcher;

    private FormFactoryInterface $factory;

    private FormBuilder $builder;

    protected function setUp(): void
    {
        $this->dispatcher = $this->createStub(EventDispatcherInterface::class);
        $this->factory = $this->createStub(FormFactoryInterface::class);
        $this->builder = new FormBuilder('name', null, $this->dispatcher, $this->factory);
        $this->factory->method('createNamedBuilder')->willReturn($this->builder);
    }

    public function testGetChildren(): void
    {
        $this->builder->add('name', TextType::class);
        $iterator = new FormBuilderIterator($this->builder);
        static::assertInstanceOf(\get_class($iterator), $iterator->getChildren());
        static::assertSame('name_name', $iterator->key());
    }

    public function testHasChildren(): void
    {
        $this->builder->add('name', TextType::class);
        $iterator = new FormBuilderIterator($this->builder);
        static::assertTrue($iterator->hasChildren());
    }
}
