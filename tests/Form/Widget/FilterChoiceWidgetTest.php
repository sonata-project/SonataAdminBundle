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

class FilterChoiceWidgetTest extends BaseWidgetTest
{
    protected $type = 'filter';

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testDefaultValueRendering(): void
    {
        $choice = $this->factory->create(
            $this->getChoiceClass(),
            null,
            $this->getDefaultOption()
        );

        $html = $this->renderWidget($choice->createView());

        $this->assertStringContainsString(
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

        $this->assertStringNotContainsString(
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

        $this->assertStringContainsString(
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
        return ChoiceType::class;
    }

    protected function getDefaultOption()
    {
        return [
            'placeholder' => 'Choose an option',
        ];
    }
}
