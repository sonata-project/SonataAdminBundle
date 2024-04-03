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

use PHPUnit\Framework\MockObject\Stub;
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

        self::assertMatchesXpath($html, $expression);
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

        self::assertMatchesXpath($html, $expression);
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

        self::assertMatchesXpath($html, $expression);
    }

    public function testLabelWithAdminTranslationDomain(): void
    {
        $fieldDescription = $this->createFieldDescriptionWithTranslationDomain('sonata_translation_domain');

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

        self::assertMatchesXpath($html, $expression);
    }

    public function testHelp(): void
    {
        $form = $this->factory->createNamed('name', TextType::class, null, [
            'help' => 'Help text test!',
        ]);
        $view = $form->createView();
        $html = $this->renderHelp($view);

        // TODO: Replace "/p|div" with "/div" when support for Symfony < 6.1 is dropped.
        $expression = <<<'EOD'
            /p|div
                [@id="name_help"]
                [@class="help-block sonata-ba-field-help help-text"]
                [.="[trans]Help text test![/trans]"]
            EOD;

        self::assertMatchesXpath($html, $expression);
    }

    public function testHelpWithAdminTranslationDomain(): void
    {
        $fieldDescription = $this->createFieldDescriptionWithTranslationDomain('sonata_translation_domain');

        $form = $this->factory->createNamed('name', TextType::class, null, [
            'help' => 'Help text test!',
            'sonata_field_description' => $fieldDescription,
        ]);
        $view = $form->createView();
        $html = $this->renderHelp($view);

        // TODO: Replace "/p|div" with "/div" when support for Symfony < 6.1 is dropped.
        $expression = <<<'EOD'
            /p|div
                [@id="name_help"]
                [@class="help-block sonata-ba-field-help help-text"]
                [.="[trans domain=sonata_translation_domain]Help text test![/trans]"]
            EOD;

        self::assertMatchesXpath($html, $expression);
    }

    public function testRowSetId(): void
    {
        $form = $this->factory->createNamed('name', TextType::class);
        $view = $form->createView();
        $html = $this->renderRow($view);

        static::assertStringContainsString(
            '<div id="sonata-ba-field-container-name" class="form-group">',
            $html
        );
    }

    public function testRowWithErrors(): void
    {
        $form = $this->factory->createNamed('name', TextType::class);
        $form->addError(new FormError('[trans]Error 1[/trans]'));
        $form->addError(new FormError('[trans]Error 2[/trans]'));
        $form->submit([]);
        $view = $form->createView();
        $html = $this->renderRow($view);

        static::assertStringContainsString(
            '<div id="sonata-ba-field-container-name" class="form-group has-error">',
            $html
        );
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
                                    ./i[@class="fas fa-exclamation-circle"]
                                ]
                            /following-sibling::li
                                [.=" [trans]Error 2[/trans]"]
                                [
                                    ./i[@class="fas fa-exclamation-circle"]
                                ]
                        ]
                        [count(./li)=2]
                ]
            EOD;

        self::assertMatchesXpath(
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

        static::assertStringContainsString(
            '<div class="foo form-group" data-value="bar" id="sonata-ba-field-container-name">',
            $html
        );
    }

    /**
     * @return Stub&FieldDescriptionInterface
     */
    private function createFieldDescriptionWithTranslationDomain(string $translationDomain): Stub
    {
        $fieldDescription = $this->createStub(FieldDescriptionInterface::class);

        $admin = $this->createStub(AdminInterface::class);
        $admin
            ->method('getCode')
            ->willReturn('sonata_code');

        $admin
            ->method('getTranslationDomain')
            ->willReturn($translationDomain);

        $fieldDescription
            ->method('getAdmin')
            ->willReturn($admin);

        return $fieldDescription;
    }
}
