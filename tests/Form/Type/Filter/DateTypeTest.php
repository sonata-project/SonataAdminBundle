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

namespace SensioLabs\AdminBundle\Tests\Form\Type\Filter;

use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use SensioLabs\AdminBundle\Form\Type\Filter\DateType;

/**
 * NEXT_MAJOR: Remove this class.
 */
#[IgnoreDeprecations]
final class DateTypeTest extends BaseTypeTestCase
{
    public function testDefaultOptions(): void
    {
        $form = $this->factory->create($this->getTestedType());

        $view = $form->createView();

        static::assertFalse($view->children['type']->vars['required']);
        static::assertFalse($view->children['value']->vars['required']);
    }

    protected function getTestedType(): string
    {
        return DateType::class;
    }
}
