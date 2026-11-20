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

use PHPUnit\Framework\Attributes\DataProvider;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\AdminBundle\Model\ModelManagerInterface;

final class FormSonataModelListWidgetTest extends BaseWidgetTestCase
{
    protected $type = 'form';

    private bool $hasEditAccess = true;

    public function testRendersStimulusControllerWithoutInlineScript(): void
    {
        $html = $this->renderModelListWidget();

        static::assertStringContainsString('data-controller="sonata-model-list"', $html);
        static::assertStringNotContainsString('<script', $html);
        static::assertStringNotContainsString('onclick=', $html);
        static::assertStringNotContainsString('start_field_dialog_form_', $html);

        static::assertStringContainsString('data-sonata-model-list-short-object-description-url-value="', $html);
        static::assertStringContainsString('data-sonata-model-list-loading-information-value="[trans]loading_information[/trans]"', $html);
        static::assertStringContainsString('data-sonata-model-list-placeholder-value="[trans]short_object_description_placeholder[/trans]"', $html);
    }

    /**
     * @phpstan-return iterable<string, array{string}>
     */
    public static function provideButtonsAreBoundToTheControllerCases(): iterable
    {
        yield 'list' => ['list'];
        yield 'add' => ['add'];
        yield 'edit' => ['edit'];
        yield 'remove' => ['remove'];
    }

    #[DataProvider('provideButtonsAreBoundToTheControllerCases')]
    public function testButtonsAreBoundToTheController(string $action): void
    {
        static::assertStringContainsString(
            \sprintf('data-action="click->sonata-model-list#%s"', $action),
            $this->renderModelListWidget()
        );
    }

    public function testTargetsAreRendered(): void
    {
        $html = $this->renderModelListWidget();

        static::assertStringContainsString('data-sonata-model-list-target="description"', $html);
        static::assertStringContainsString('data-sonata-model-list-target="editButton"', $html);
        static::assertStringContainsString('data-sonata-model-list-target="dialog"', $html);
        static::assertMatchesRegularExpression('/<input[^>]+data-sonata-model-list-target="input"/', $html);
    }

    public function testEditUrlIsEmptyWithoutEditAccess(): void
    {
        $this->hasEditAccess = false;

        $html = $this->renderModelListWidget();

        static::assertStringContainsString('data-sonata-model-list-edit-url-value=""', $html);
        static::assertStringNotContainsString('data-sonata-model-list-target="editButton"', $html);
    }

    public function testEditUrlIsRenderedWithEditAccess(): void
    {
        static::assertStringContainsString(
            'data-sonata-model-list-edit-url-value="/edit"',
            $this->renderModelListWidget()
        );
    }

    protected function getSonataAdmin(): array
    {
        $associationAdmin = static::createStub(AdminInterface::class);
        $associationAdmin->method('hasRoute')->willReturn(true);
        $associationAdmin->method('hasAccess')->willReturnCallback(
            fn (string $action): bool => 'edit' !== $action || $this->hasEditAccess
        );
        $associationAdmin->method('generateUrl')->willReturnCallback(
            static fn (string $name): string => '/'.$name
        );

        $fieldDescription = static::createStub(FieldDescriptionInterface::class);
        $fieldDescription->method('getAssociationAdmin')->willReturn($associationAdmin);
        // Every option falls back to its default value.
        $fieldDescription->method('getOption')->willReturnArgument(1);

        return ['field_description' => $fieldDescription, 'edit' => 'list'] + parent::getSonataAdmin();
    }

    private function renderModelListWidget(): string
    {
        $form = $this->factory->create(ModelListType::class, null, [
            'model_manager' => static::createStub(ModelManagerInterface::class),
            'class' => \stdClass::class,
        ]);

        return $this->cleanHtmlWhitespace($this->renderWidget($form->createView()));
    }
}
