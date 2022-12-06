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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormRegistryInterface;

/**
 * @author Mike Meier <mike.meier@ibrows.ch>
 */
final class FormBuilderIteratorTest extends TestCase
{
    private FormBuilder $builder;

    protected function setUp(): void
    {
        $dispatcher = $this->createStub(EventDispatcherInterface::class);
        $registry = $this->createStub(FormRegistryInterface::class);
        $factory = new FormFactory($registry);
        $this->builder = new FormBuilder('name', null, $dispatcher, $factory);
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
        static::assertFalse($iterator->hasChildren());
    }

    public function testCurrentCasting(): void
    {
        $this->builder->add('hungry', ChoiceType::class, [
            'multiple' => true,
            'expanded' => true,
            'choices' => [
                'Maybe' => null,
                'Yes' => true,
                'No' => false,
            ],
        ]);

        $iterator = new FormBuilderIterator($this->builder->get('hungry'));
        static::assertInstanceOf(FormBuilderInterface::class, $iterator->current());
    }
}
