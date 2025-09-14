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

namespace Sonata\AdminBundle\Tests\Form\Type\Filter;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use Sonata\AdminBundle\Form\Type\Filter\ChoiceType;

/**
 * NEXT_MAJOR: Remove this class.
 */
#[IgnoreDeprecations]
final class ChoiceTypeTest extends BaseTypeTestCase
{
    public function testDefaultOptions(): void
    {
        $form = $this->factory->create($this->getTestedType());

        $view = $form->createView();

        static::assertFalse($view->children['type']->vars['required']);
        static::assertFalse($view->children['value']->vars['required']);
    }

    /**
     * @psalm-suppress DeprecatedClass
     */
    protected function getTestedType(): string
    {
        return ChoiceType::class;
    }
}
