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

namespace Sonata\AdminBundle\Tests\Form\Extension;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Form\Extension\ChoiceTypeExtension;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Forms;

class ChoiceTypeExtensionTest extends TestCase
{
    private $factory;

    protected function setup(): void
    {
        $container = $this->getMockForAbstractClass(ContainerInterface::class);
        $container->method('has')->willReturn(true);
        $container->method('get')
            ->with($this->equalTo('sonata.admin.form.choice_extension'))
            ->willReturn(new ChoiceTypeExtension());

        $this->factory = Forms::createFormFactoryBuilder()
            ->getFormFactory();
    }

    public function testExtendedType(): void
    {
        $extension = new ChoiceTypeExtension();

        $this->assertSame(
            ChoiceType::class,
            $extension->getExtendedType()
        );

        $this->assertSame(
            [ChoiceType::class],
            ChoiceTypeExtension::getExtendedTypes()
        );
    }

    public function testDefaultOptionsWithSortable(): void
    {
        $view = $this->factory
            ->create(ChoiceType::class, null, [
                'sortable' => true,
            ])
            ->createView();

        $this->assertTrue(isset($view->vars['sortable']));
        $this->assertTrue($view->vars['sortable']);
    }

    public function testDefaultOptionsWithoutSortable(): void
    {
        $view = $this->factory
            ->create(ChoiceType::class, null, [])
            ->createView();

        $this->assertTrue(isset($view->vars['sortable']));
        $this->assertFalse($view->vars['sortable']);
    }
}
