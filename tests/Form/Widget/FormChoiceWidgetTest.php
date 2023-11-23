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

namespace Sonata\AdminBundle\Tests\Form\Widget;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpKernel\Kernel;

final class FormChoiceWidgetTest extends BaseWidgetTest
{
    protected $type = 'form';

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testLabelRendering(): void
    {
        $choices = array_flip(['some', 'choices']);

        $choice = $this->factory->create(
            $this->getChoiceClass(),
            null,
            $this->getDefaultOption() + [
                'multiple' => true,
                'expanded' => true,
            ] + compact('choices')
        );

        $html = $this->renderWidget($choice->createView());

        if (0 !== preg_match("/7\..\../", Kernel::VERSION)) {
            static::assertStringContainsString(
                '<li><div class="checkbox"><label><input type="checkbox" id="choice_0" name="choice[]" value="0" ><span class="control-label__text">[trans]some[/trans]</span></label></div></li>',
                $this->cleanHtmlWhitespace($html)
            );
        } else {
            static::assertStringContainsString(
                '<li><div class="checkbox"><label><input type="checkbox" id="choice_0" name="choice[]" value="0" /><span class="control-label__text">[trans]some[/trans]</span></label></div></li>',
                $this->cleanHtmlWhitespace($html)
            );
        }
    }

    public function testDefaultValueRendering(): void
    {
        $choice = $this->factory->create(
            $this->getChoiceClass(),
            null,
            $this->getDefaultOption()
        );

        $html = $this->renderWidget($choice->createView());

        static::assertStringContainsString(
            '<option value="" selected="selected">[trans]Choose an option[/trans]</option>',
            $this->cleanHtmlWhitespace($html)
        );
    }

    public function testRequiredIsDisabledForEmptyPlaceholder(): void
    {
        $choice = $this->factory->create(
            $this->getChoiceClass(),
            null,
            $this->getRequiredOption()
        );

        $html = $this->renderWidget($choice->createView());

        static::assertStringNotContainsString(
            'required="required"',
            $this->cleanHtmlWhitespace($html)
        );
    }

    public function testRequiredIsEnabledIfPlaceholderIsSet(): void
    {
        $choice = $this->factory->create(
            $this->getChoiceClass(),
            null,
            array_merge($this->getRequiredOption(), $this->getDefaultOption())
        );

        $html = $this->renderWidget($choice->createView());

        static::assertStringContainsString(
            'required="required"',
            $this->cleanHtmlWhitespace($html)
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function getRequiredOption(): array
    {
        return ['required' => true];
    }

    /**
     * @return class-string<FormTypeInterface>
     */
    protected function getChoiceClass(): string
    {
        return ChoiceType::class;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getDefaultOption(): array
    {
        return [
            'placeholder' => 'Choose an option',
        ];
    }
}
