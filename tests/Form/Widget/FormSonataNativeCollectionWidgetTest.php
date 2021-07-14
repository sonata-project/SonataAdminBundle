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

use Sonata\AdminBundle\Form\Extension\Field\Type\FormTypeFieldExtension;
use Sonata\AdminBundle\Form\Type\CollectionType;
use Sonata\AdminBundle\Tests\Fixtures\TestExtension;
use Symfony\Component\Form\FormExtensionInterface;
use Symfony\Component\Form\FormTypeGuesserInterface;
use Symfony\Component\Form\FormTypeInterface;

final class FormSonataNativeCollectionWidgetTest extends BaseWidgetTest
{
    protected $type = 'form';

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @phpstan-return array<array{array<string, mixed>}>
     */
    public function prototypeRenderingProvider(): array
    {
        return [
            'shrinkable collection' => [['allow_delete' => true]],
            'unshrinkable collection' => [['allow_delete' => false]],
        ];
    }

    /**
     * @param array<string, mixed> $options
     *
     * @dataProvider prototypeRenderingProvider
     */
    public function testPrototypeIsDeletableNoMatterTheShrinkability(array $options): void
    {
        $choice = $this->factory->create(
            $this->getChoiceClass(),
            null,
            ['allow_add' => true] + $options
        );

        $html = $this->renderWidget($choice->createView());

        self::assertStringContainsString(
            'sonata-collection-delete',
            $this->cleanHtmlWhitespace($html)
        );
    }

    /**
     * @phpstan-return array<FormExtensionInterface>
     */
    protected function getExtensions(): array
    {
        $extensions = parent::getExtensions();
        $guesser = $this->createMock(FormTypeGuesserInterface::class);
        $extension = new TestExtension($guesser);

        $extension->addTypeExtension(new FormTypeFieldExtension([], [
            'form_type' => 'vertical',
        ]));
        $extensions[] = $extension;

        return $extensions;
    }

    /**
     * @return class-string<FormTypeInterface>
     */
    protected function getChoiceClass(): string
    {
        return CollectionType::class;
    }
}
