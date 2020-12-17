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
use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * @author Mike Meier <mike.meier@ibrows.ch>
 */
class FormBuilderIteratorTest extends TestCase
{
    use ExpectDeprecationTrait;

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

    protected function setUp(): void
    {
        $this->dispatcher = $this->createStub(EventDispatcherInterface::class);
        $this->factory = $this->createStub(FormFactoryInterface::class);
        $this->builder = new FormBuilder('name', null, $this->dispatcher, $this->factory);
        $this->factory->method('createNamedBuilder')->willReturn($this->builder);
    }

    protected function tearDown(): void
    {
        $this->dispatcher = null;
        $this->factory = null;
        $this->builder = null;
    }

    public function testGetChildren(): void
    {
        $this->builder->add('name', TextType::class);
        $iterator = new FormBuilderIterator($this->builder);
        $this->assertInstanceOf(\get_class($iterator), $iterator->getChildren());
        $this->assertSame('name_name', $iterator->key());
    }

    public function testHasChildren(): void
    {
        $this->builder->add('name', TextType::class);
        $iterator = new FormBuilderIterator($this->builder);
        $this->assertTrue($iterator->hasChildren());
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testTriggersADeprecationWithWrongPrefixType(): void
    {
        $this->expectDeprecation('Passing other type than string or null as argument 2 for method Sonata\AdminBundle\Util\FormBuilderIterator::__construct() is deprecated since sonata-project/admin-bundle 3.84. It will accept only string and null in version 4.0.');

        $this->builder->add('name', TextType::class);
        $iterator = new FormBuilderIterator($this->builder, new \stdClass());

        $this->assertSame($iterator->key(), 'name_name');
    }
}
