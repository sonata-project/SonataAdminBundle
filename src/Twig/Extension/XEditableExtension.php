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
use Sonata\AdminBundle\Twig\XEditableRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class XEditableExtension extends AbstractExtension
{
    // NEXT_MAJOR: Remove this const.
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
     * NEXT_MAJOR: Remove this constructor.
     *
     * @internal This class should only be used through Twig
     */
    public function __construct(
        private XEditableRuntime $xEditableRuntime,
    ) {
    }

    /**
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter(
                'sonata_xeditable_type',
                [XEditableRuntime::class, 'getXEditableType']
            ),
            new TwigFilter(
                'sonata_xeditable_choices',
                [XEditableRuntime::class, 'getXEditableChoices']
            ),
        ];
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle version 4.7 use XEditableRuntime::getXEditableType() instead
     */
    public function getXEditableType(?string $type): string|bool
    {
        @trigger_error(\sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 4.7 and will be removed in 5.0.'
            .'  Use "%s::%s()" instead.',
            __METHOD__,
            XEditableRuntime::class,
            __FUNCTION__
        ), \E_USER_DEPRECATED);

        return $this->xEditableRuntime->getXEditableType($type);
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle version 4.7 use XEditableRuntime::getXEditableChoices() instead
     *
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
        @trigger_error(\sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 4.7 and will be removed in 5.0.'
            .'  Use "%s::%s()" instead.',
            __METHOD__,
            XEditableRuntime::class,
            __FUNCTION__
        ), \E_USER_DEPRECATED);

        return $this->xEditableRuntime->getXEditableChoices($fieldDescription);
    }
}
