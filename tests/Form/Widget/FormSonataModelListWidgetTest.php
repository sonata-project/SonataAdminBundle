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
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\Loader\ChainLoader;

final class FormSonataModelListWidgetTest extends BaseWidgetTestCase
{
    protected $type = 'form';

    /**
     * @phpstan-return iterable<string, array{bool}>
     */
    public static function provideActionButtonsAreRenderedDependingOnDisabledStateCases(): iterable
    {
        yield 'enabled field' => [false];
        yield 'disabled field' => [true];
    }

    #[DataProvider('provideActionButtonsAreRenderedDependingOnDisabledStateCases')]
    public function testActionButtonsAreRenderedDependingOnDisabledState(bool $disabled): void
    {
        $form = $this->factory->create(ModelListType::class, null, [
            'model_manager' => static::createStub(ModelManagerInterface::class),
            'class' => \stdClass::class,
            'disabled' => $disabled,
        ]);

        $html = $this->cleanHtmlWhitespace($this->renderWidget($form->createView()));

        if ($disabled) {
            static::assertStringContainsString('field-actions', $html);
            static::assertStringContainsString('btn-warning', $html);
            static::assertStringNotContainsString('btn-info', $html);
            static::assertStringNotContainsString('btn-success', $html);
            static::assertStringNotContainsString('btn-danger', $html);
        } else {
            static::assertStringContainsString('field-actions', $html);
            static::assertStringContainsString('btn-info', $html);
            static::assertStringContainsString('btn-success', $html);
            static::assertStringContainsString('btn-danger', $html);
        }
    }

    protected function getEnvironment(): Environment
    {
        $environment = parent::getEnvironment();

        // The JavaScript included by the widget is not relevant here and needs a lot of unrelated Twig functions.
        $environment->setLoader(new ChainLoader([
            new ArrayLoader(['@SonataAdmin/CRUD/Association/edit_many_script.html.twig' => '']),
            $environment->getLoader(),
        ]));

        return $environment;
    }

    protected function getSonataAdmin(): array
    {
        $associationAdmin = static::createStub(AdminInterface::class);
        $associationAdmin->method('hasRoute')->willReturn(true);
        $associationAdmin->method('hasAccess')->willReturn(true);
        $associationAdmin->method('generateUrl')->willReturn('/url');

        $fieldDescription = static::createStub(FieldDescriptionInterface::class);
        $fieldDescription->method('getAssociationAdmin')->willReturn($associationAdmin);

        return ['field_description' => $fieldDescription] + parent::getSonataAdmin();
    }
}
