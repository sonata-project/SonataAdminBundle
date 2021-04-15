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

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;

final class AdminLayoutTest extends AbstractLayoutTestCase
{
    public function testLabel(): void
    {
        $form = $this->factory->createNamed('name', TextType::class);
        $html = $this->renderLabel($form->createView());

        $expression = <<<'EOD'
/label
    [@class="col-sm-3 control-label required"]
    [@for="name"]
    [.="[trans]Name[/trans]"]
EOD;

        $this->assertMatchesXpath($html, $expression);
    }

    public function testLabelWithoutTranslation(): void
    {
        $form = $this->factory->createNamed(
            'name',
            TextType::class,
            null,
            [
            'translation_domain' => false,
        ]
        );
        $html = $this->renderLabel($form->createView());

        $expression = <<<'EOD'
/label
    [@class="col-sm-3 control-label required"]
    [@for="name"]
    [.="Name"]
EOD;

        $this->assertMatchesXpath($html, $expression);
    }

    public function testLabelWithCustomTranslationDomain(): void
    {
        $form = $this->factory->createNamed(
            'name',
            TextType::class,
            null,
            [
            'translation_domain' => 'custom_domain',
        ]
        );
        $html = $this->renderLabel($form->createView());

        $expression = <<<'EOD'
/label
    [@class="col-sm-3 control-label required"]
    [@for="name"]
    [.="[trans domain=custom_domain]Name[/trans]"]
EOD;

        $this->assertMatchesXpath($html, $expression);
    }

    public function testLabelWithAdminTranslationDomain(): void
    {
        $fieldDescription = $this->createStub(FieldDescriptionInterface::class);

        $admin = $this->createStub(AdminInterface::class);
        $admin
            ->method('getCode')
            ->willReturn('sonata_code');

        $admin
            ->method('getTranslationDomain')
            ->willReturn('sonata_translation_domain');

        $fieldDescription
            ->method('getAdmin')
            ->willReturn($admin);

        $form = $this->factory->createNamed('name', TextType::class, null, [
            'sonata_field_description' => $fieldDescription,
        ]);
        $html = $this->renderLabel($form->createView());

        $expression = <<<'EOD'
/label
    [@class="col-sm-3 control-label required"]
    [@for="name"]
    [.="[trans domain=sonata_translation_domain]Name[/trans]"]
EOD;

        $this->assertMatchesXpath($html, $expression);
    }

    public function testHelp(): void
    {
        $form = $this->factory->createNamed('name', TextType::class, null, [
            'help' => 'Help text test!',
        ]);
        $view = $form->createView();
        $html = $this->renderHelp($view);

        $expression = <<<'EOD'
/p
    [@id="name_help"]
    [@class="help-block sonata-ba-field-widget-help sonata-ba-field-help help-text"]
    [.="[trans]Help text test![/trans]"]
EOD;

        $this->assertMatchesXpath($html, $expression);
    }

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
        $form->submit([]);
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

    public function testRowAttr(): void
    {
        $form = $this->factory->createNamed('name', TextType::class, '', [
            'row_attr' => [
                'class' => 'foo',
                'data-value' => 'bar',
            ],
        ]);
        $view = $form->createView();
        $html = $this->renderRow($view);

        $this->assertMatchesXpath(
            $html,
            '//div[@class="foo form-group"][@data-value="bar"][@id="sonata-ba-field-container-name"]'
        );
    }
}
