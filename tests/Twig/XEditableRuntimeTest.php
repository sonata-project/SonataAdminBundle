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

namespace Sonata\AdminBundle\Tests\Twig;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Twig\XEditableRuntime;
use Symfony\Component\Translation\Translator;

final class XEditableRuntimeTest extends TestCase
{
    /**
     * @param array<string, mixed>         $options
     * @param array<array<string, string>> $expectedChoices
     *
     * @dataProvider xEditablechoicesProvider
     */
    public function testGetXEditableChoicesIsIdempotent(array $options, array $expectedChoices): void
    {
        $xEditableRuntime = new XEditableRuntime(new Translator('en'));

        $fieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $fieldDescription
            ->method('getOption')
            ->withConsecutive(
                ['choices', []],
                ['catalogue'],
                ['required'],
                ['multiple']
            )
            ->will(static::onConsecutiveCalls(
                $options['choices'],
                'MyCatalogue',
                $options['multiple'] ?? null
            ));

        static::assertSame($expectedChoices, $xEditableRuntime->getXEditableChoices($fieldDescription));
    }

    /**
     * @phpstan-return array<string, array{
     *	array<string, mixed>,
     *	array<array{value: string, text: string}>
     * }>
     */
    public function xEditableChoicesProvider(): iterable
    {
        return [
            'needs processing' => [
                ['choices' => ['Status1' => 'Alias1', 'Status2' => 'Alias2']],
                [
                    ['value' => 'Status1', 'text' => 'Alias1'],
                    ['value' => 'Status2', 'text' => 'Alias2'],
                ],
            ],
            'already processed' => [
                ['choices' => [
                    ['value' => 'Status1', 'text' => 'Alias1'],
                    ['value' => 'Status2', 'text' => 'Alias2'],
                ]],
                [
                    ['value' => 'Status1', 'text' => 'Alias1'],
                    ['value' => 'Status2', 'text' => 'Alias2'],
                ],
            ],
            'not required' => [
                [
                    'required' => false,
                    'choices' => ['' => '', 'Status1' => 'Alias1', 'Status2' => 'Alias2'],
                ],
                [
                    ['value' => '', 'text' => ''],
                    ['value' => 'Status1', 'text' => 'Alias1'],
                    ['value' => 'Status2', 'text' => 'Alias2'],
                ],
            ],
            'not required multiple' => [
                [
                    'required' => false,
                    'multiple' => true,
                    'choices' => ['Status1' => 'Alias1', 'Status2' => 'Alias2'],
                ],
                [
                    ['value' => 'Status1', 'text' => 'Alias1'],
                    ['value' => 'Status2', 'text' => 'Alias2'],
                ],
            ],
        ];
    }
}
