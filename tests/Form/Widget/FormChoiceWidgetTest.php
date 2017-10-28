<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Form\Widget;

class FormChoiceWidgetTest extends BaseWidgetTest
{
    protected $type = 'form';

    public function setUp()
    {
        parent::setUp();
    }

    public function testLabelRendering()
    {
        $choices = ['some', 'choices'];
        if (!method_exists('Symfony\Component\Form\FormTypeInterface', 'setDefaultOptions')) {
            $choices = array_flip($choices);
        }

        $choice = $this->factory->create(
            $this->getChoiceClass(),
            null,
            $this->getDefaultOption() + [
                'multiple' => true,
                'expanded' => true,
            ] + compact('choices')
        );

        $html = $this->renderWidget($choice->createView());

        $this->assertContains(
            '<li><div class="checkbox"><label><input type="checkbox" id="choice_0" name="choice[]" value="0" /><span class="control-label__text">[trans]some[/trans]</span></label></div></li>',
            $this->cleanHtmlWhitespace($html)
        );
    }

    public function testDefaultValueRendering()
    {
        $choice = $this->factory->create(
            $this->getChoiceClass(),
            null,
            $this->getDefaultOption()
        );

        $html = $this->renderWidget($choice->createView());

        $this->assertContains(
            '<option value="" selected="selected">[trans]Choose an option[/trans]</option>',
            $this->cleanHtmlWhitespace($html)
        );
    }

    public function testRequiredIsDisabledForEmptyPlaceholder()
    {
        $choice = $this->factory->create(
            $this->getChoiceClass(),
            null,
            $this->getRequiredOption()
        );

        $html = $this->renderWidget($choice->createView());

        $this->assertNotContains(
            'required="required"',
            $this->cleanHtmlWhitespace($html)
        );
    }

    public function testRequiredIsEnabledIfPlaceholderIsSet()
    {
        $choice = $this->factory->create(
            $this->getChoiceClass(),
            null,
            array_merge($this->getRequiredOption(), $this->getDefaultOption())
        );

        $html = $this->renderWidget($choice->createView());

        $this->assertContains(
            'required="required"',
            $this->cleanHtmlWhitespace($html)
        );
    }

    protected function getRequiredOption()
    {
        return ['required' => true];
    }

    protected function getChoiceClass()
    {
        return 'Symfony\Component\Form\Extension\Core\Type\ChoiceType';
    }

    protected function getDefaultOption()
    {
        return [
            'placeholder' => 'Choose an option',
        ];
    }
}
