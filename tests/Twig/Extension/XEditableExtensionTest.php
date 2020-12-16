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
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Twig\Extension\XEditableExtension;
use Symfony\Component\Translation\Translator;

/**
 * Test for SonataAdminExtension.
 *
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
final class XEditableExtensionTest extends TestCase
{
    /**
     * @dataProvider xEditablechoicesProvider
     */
    public function testGetXEditableChoicesIsIdempotent(array $options, array $expectedChoices): void
    {
        $xEditableTypeMapping = [
            'choice' => 'select',
            'boolean' => 'select',
            'text' => 'text',
            'textarea' => 'textarea',
            'html' => 'textarea',
            'email' => 'email',
            'string' => 'text',
            'smallint' => 'text',
            'bigint' => 'text',
            'integer' => 'number',
            'decimal' => 'number',
            'currency' => 'number',
            'percent' => 'number',
            'url' => 'url',
        ];

        $twigExtension = new XEditableExtension(new Translator('en'), $xEditableTypeMapping);

        $fieldDescription = $this->getMockForAbstractClass(FieldDescriptionInterface::class);
        $fieldDescription
            ->method('getOption')
            ->withConsecutive(
                ['choices', []],
                ['catalogue'],
                ['required'],
                ['multiple']
            )
            ->will($this->onConsecutiveCalls(
                $options['choices'],
                'MyCatalogue',
                $options['multiple'] ?? null
            ));

        $this->assertSame($expectedChoices, $twigExtension->getXEditableChoices($fieldDescription));
    }

    public function xEditableChoicesProvider()
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
