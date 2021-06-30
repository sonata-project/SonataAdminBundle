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

namespace Sonata\AdminBundle\Twig\Extension;

use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class XEditableExtension extends AbstractExtension
{
    public const FIELD_DESCRIPTION_MAPPING = [
        FieldDescriptionInterface::TYPE_CHOICE => 'select',
        FieldDescriptionInterface::TYPE_BOOLEAN => 'select',
        FieldDescriptionInterface::TYPE_TEXTAREA => 'textarea',
        FieldDescriptionInterface::TYPE_HTML => 'textarea',
        FieldDescriptionInterface::TYPE_EMAIL => 'email',
        FieldDescriptionInterface::TYPE_STRING => 'text',
        FieldDescriptionInterface::TYPE_INTEGER => 'number',
        FieldDescriptionInterface::TYPE_FLOAT => 'number',
        FieldDescriptionInterface::TYPE_CURRENCY => 'number',
        FieldDescriptionInterface::TYPE_PERCENT => 'number',
        FieldDescriptionInterface::TYPE_URL => 'url',
    ];

    /**
     * @var string[]
     */
    private $xEditableTypeMapping = [];

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @param string[] $xEditableTypeMapping
     *
     * @internal This class should only be used through Twig
     */
    public function __construct(
        TranslatorInterface $translator,
        array $xEditableTypeMapping = self::FIELD_DESCRIPTION_MAPPING
    ) {
        $this->translator = $translator;
        $this->xEditableTypeMapping = $xEditableTypeMapping;
    }

    /**
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter(
                'sonata_xeditable_type',
                [$this, 'getXEditableType']
            ),
            new TwigFilter(
                'sonata_xeditable_choices',
                [$this, 'getXEditableChoices']
            ),
        ];
    }

    /**
     * @return string|bool
     */
    public function getXEditableType(?string $type)
    {
        if (null === $type) {
            return false;
        }

        return $this->xEditableTypeMapping[$type] ?? false;
    }

    /**
     * Return xEditable choices based on the field description choices options & catalogue options.
     * With the following choice options:
     *     ['Status1' => 'Alias1', 'Status2' => 'Alias2']
     * The method will return:
     *     [['value' => 'Status1', 'text' => 'Alias1'], ['value' => 'Status2', 'text' => 'Alias2']].
     *
     * @phpstan-return array<array{value: string, text: string}>
     */
    public function getXEditableChoices(FieldDescriptionInterface $fieldDescription): array
    {
        $choices = $fieldDescription->getOption('choices', []);
        $catalogue = $fieldDescription->getOption('catalogue');

        reset($choices);
        $first = current($choices);
        if (\is_array($first)) {
            // the choice are already in the right format
            $xEditableChoices = $choices;
        } else {
            $xEditableChoices = [];
            foreach ($choices as $value => $text) {
                if (\is_array($text)) {
                    // the choice is already in the right format
                    $xEditableChoices[] = $text;
                    break;
                }

                if (null !== $catalogue) {
                    $text = $this->translator->trans($text, [], $catalogue);
                }

                $xEditableChoices[] = [
                    'value' => $value,
                    'text' => $text,
                ];
            }
        }

        if (
            false === $fieldDescription->getOption('required', true)
            && false === $fieldDescription->getOption('multiple', false)
        ) {
            $xEditableChoices = array_merge([[
                'value' => '',
                'text' => '',
            ]], $xEditableChoices);
        }

        return $xEditableChoices;
    }
}
