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

use Sonata\AdminBundle\Form\Type\Filter\ChoiceType;
use Symfony\Component\Translation\TranslatorInterface;

final class ChoiceTypeTest extends BaseTypeTest
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
        return ChoiceType::class;
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @return ChoiceType[]
     */
    protected function getTypes(): array
    {
        return [
            new ChoiceType($this->createStub(TranslatorInterface::class)),
        ];
    }
}
