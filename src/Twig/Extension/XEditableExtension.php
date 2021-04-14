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
use Sonata\AdminBundle\Templating\TemplateRegistryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class XEditableExtension extends AbstractExtension
{
    public const FIELD_DESCRIPTION_MAPPING = [
        FieldDescriptionInterface::TYPE_CHOICE => 'select',
        FieldDescriptionInterface::TYPE_BOOLEAN => 'select',
        TemplateRegistryInterface::TYPE_TEXT => 'text', // NEXT_MAJOR: Remove this line.
        FieldDescriptionInterface::TYPE_TEXTAREA => 'textarea',
        FieldDescriptionInterface::TYPE_HTML => 'textarea',
        FieldDescriptionInterface::TYPE_EMAIL => 'email',
        FieldDescriptionInterface::TYPE_STRING => 'text',
        TemplateRegistryInterface::TYPE_SMALLINT => 'text', // NEXT_MAJOR: Remove this line.
        TemplateRegistryInterface::TYPE_BIGINT => 'text', // NEXT_MAJOR: Remove this line.
        FieldDescriptionInterface::TYPE_INTEGER => 'number',
        FieldDescriptionInterface::TYPE_FLOAT => 'number',
        TemplateRegistryInterface::TYPE_DECIMAL => 'number', // NEXT_MAJOR: Remove this line.
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
            //NEXT_MAJOR: Uncomment lines below
            /*
            new TwigFilter(
                'sonata_xeditable_type',
                [$this, 'getXEditableType']
            ),
            new TwigFilter(
                'sonata_xeditable_choices',
                [$this, 'getXEditableChoices']
            ),
            */
        ];
    }

    /**
     * @return string|bool
     */
    public function getXEditableType(string $type)
    {
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
        $xEditableChoices = [];
        if (!empty($choices)) {
            reset($choices);
            $first = current($choices);
            // the choices are already in the right format
            if (\is_array($first) && \array_key_exists('value', $first) && \array_key_exists('text', $first)) {
                $xEditableChoices = $choices;
            } else {
                foreach ($choices as $value => $text) {
                    if ($catalogue) {
                        $text = $this->translator->trans($text, [], $catalogue);
                    }

                    $xEditableChoices[] = [
                        'value' => $value,
                        'text' => $text,
                    ];
                }
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
