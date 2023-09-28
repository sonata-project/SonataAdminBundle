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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Forms;

final class ChoiceTypeExtensionTest extends TestCase
{
    private FormFactoryInterface $factory;

    protected function setUp(): void
    {
        $this->factory = Forms::createFormFactoryBuilder()
            ->addTypeExtensions([new ChoiceTypeExtension()])
            ->getFormFactory();
    }

    public function testExtendedType(): void
    {
        static::assertSame(
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

        static::assertArrayHasKey('sortable', $view->vars);
        static::assertTrue($view->vars['sortable']);
    }

    public function testDefaultOptionsWithoutSortable(): void
    {
        $view = $this->factory
            ->create(ChoiceType::class, null, [])
            ->createView();

        static::assertArrayHasKey('sortable', $view->vars);
        static::assertFalse($view->vars['sortable']);
    }
}
