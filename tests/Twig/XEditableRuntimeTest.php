<?php

declare(strict_types=1);

/*
 * This file is part of sensiolabs-de/admin-bundle.
 *
 * (c) SensioLabs Deutschland <info@sensiolabs.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\AdminBundle\Tests\Twig;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SensioLabs\AdminBundle\FieldDescription\FieldDescriptionInterface;
use SensioLabs\AdminBundle\Tests\Fixtures\Enum\Suit;
use SensioLabs\AdminBundle\Twig\XEditableRuntime;
use Symfony\Component\Translation\Translator;

final class XEditableRuntimeTest extends TestCase
{
    /**
     * @param array<string, mixed>         $options
     * @param array<array<string, string>> $expectedChoices
     */
    #[DataProvider('provideGetXEditableChoicesIsIdempotentCases')]
    public function testGetXEditableChoicesIsIdempotent(array $options, array $expectedChoices): void
    {
        $xEditableRuntime = new XEditableRuntime(new Translator('en'));

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

        static::assertSame($expectedChoices, $xEditableRuntime->getXEditableChoices($fieldDescription));
    }

    /**
     * @phpstan-return iterable<string, array{
     *     array<string, mixed>,
     *     array<array{value: string, text: string}>
     * }>
     */
    public static function provideGetXEditableChoicesIsIdempotentCases(): iterable
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

        yield 'enum cases' => [
            [
                'required' => false,
                'multiple' => false,
                'choices' => [Suit::Hearts, Suit::Clubs],
            ],
            [
                ['value' => 'H', 'text' => 'Hearts'],
                ['value' => 'C', 'text' => 'Clubs'],
            ],
        ];
    }
}
