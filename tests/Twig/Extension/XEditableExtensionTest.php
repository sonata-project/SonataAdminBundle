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

namespace Sonata\AdminBundle\Tests\Twig\Extension;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Twig\Extension\XEditableExtension;
use Sonata\AdminBundle\Twig\XEditableRuntime;
use Symfony\Component\Translation\Translator;

/**
 * NEXT_MAJOR: Remove this test.
 *
 * @group legacy
 */
final class XEditableExtensionTest extends TestCase
{
    /**
     * @param array<string, mixed>         $options
     * @param array<array<string, string>> $expectedChoices
     *
     * @dataProvider xEditableChoicesProvider
     *
     * @psalm-suppress DeprecatedMethod
     */
    public function testGetXEditableChoicesIsIdempotent(array $options, array $expectedChoices): void
    {
        $twigExtension = new XEditableExtension(new XEditableRuntime(new Translator('en')));

        $fieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $fieldDescription
            ->method('getOption')
            ->willReturnMap([
                ['choices', [], $options['choices']],
                ['catalogue', null, 'MyCatalogue'],
                ['choice_translation_domain', null, 'MyCatalogue'],
                ['required', null, $options['multiple'] ?? null],
                ['multiple', null, null],
            ]);

        static::assertSame($expectedChoices, $twigExtension->getXEditableChoices($fieldDescription));
    }

    /**
     * @phpstan-return array<string, array{
     *	array<string, mixed>,
     *	array<array{value: string, text: string}>
     * }>
     */
    public function xEditableChoicesProvider(): iterable
    {
        yield 'needs processing' => [
            ['choices' => ['Status1' => 'Alias1', 'Status2' => 'Alias2']],
            [
                ['value' => 'Status1', 'text' => 'Alias1'],
                ['value' => 'Status2', 'text' => 'Alias2'],
            ],
        ];
        yield 'already processed' => [
            ['choices' => [
                ['value' => 'Status1', 'text' => 'Alias1'],
                ['value' => 'Status2', 'text' => 'Alias2'],
            ]],
            [
                ['value' => 'Status1', 'text' => 'Alias1'],
                ['value' => 'Status2', 'text' => 'Alias2'],
            ],
        ];
        yield 'not required' => [
            [
                'required' => false,
                'choices' => ['' => '', 'Status1' => 'Alias1', 'Status2' => 'Alias2'],
            ],
            [
                ['value' => '', 'text' => ''],
                ['value' => 'Status1', 'text' => 'Alias1'],
                ['value' => 'Status2', 'text' => 'Alias2'],
            ],
        ];
        yield 'not required multiple' => [
            [
                'required' => false,
                'multiple' => true,
                'choices' => ['Status1' => 'Alias1', 'Status2' => 'Alias2'],
            ],
            [
                ['value' => 'Status1', 'text' => 'Alias1'],
                ['value' => 'Status2', 'text' => 'Alias2'],
            ],
        ];
    }
}
