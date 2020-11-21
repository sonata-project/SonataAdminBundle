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

namespace Sonata\AdminBundle\Tests\Form;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;

final class AdminLayoutTestCase extends AbstractLayoutTest
{
    public function testRowSetId(): void
    {
        $form = $this->factory->createNamed('name', TextType::class);
        $view = $form->createView();
        $html = $this->renderRow($view);

        $this->assertMatchesXpath($html, '//div[@class="form-group"][@id="sonata-ba-field-container-name"]');
    }

    public function testRowWithErrors(): void
    {
        $form = $this->factory->createNamed('name', TextType::class);
        $form->addError(new FormError('[trans]Error 1[/trans]'));
        $form->addError(new FormError('[trans]Error 2[/trans]'));
        $view = $form->createView();
        $html = $this->renderRow($view);

        $this->assertMatchesXpath($html, '/div[@class="form-group has-error"][@id="sonata-ba-field-container-name"]');
    }

    public function testErrors(): void
    {
        $form = $this->factory->createNamed('name', TextType::class);
        $form->addError(new FormError('[trans]Error 1[/trans]'));
        $form->addError(new FormError('[trans]Error 2[/trans]'));
        $view = $form->createView();
        $html = $this->renderErrors($view);

        $expression = <<<'EOD'
/div
    [@class="alert alert-danger"]
    [
        ./ul
            [@class="list-unstyled"]
            [
                ./li
                    [.=" [trans]Error 1[/trans]"]
                    [
                        ./i[@class="fa fa-exclamation-circle"]
                    ]
                /following-sibling::li
                    [.=" [trans]Error 2[/trans]"]
                    [
                        ./i[@class="fa fa-exclamation-circle"]
                    ]
            ]
            [count(./li)=2]
    ]
EOD;

        $this->assertMatchesXpath(
            $html,
            $expression
        );
    }
}
