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

namespace SensioLabs\AdminBundle\Builder;

use SensioLabs\AdminBundle\FieldDescription\FieldDescriptionCollection;
use SensioLabs\AdminBundle\FieldDescription\FieldDescriptionInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface ListBuilderInterface
{
    /**
     * Adds missing information to the given field description.
     */
    public function fixFieldDescription(FieldDescriptionInterface $fieldDescription): void;

    /**
     * @param array<string, mixed> $options
     *
     * @return FieldDescriptionCollection<FieldDescriptionInterface>
     */
    public function getBaseList(array $options = []): FieldDescriptionCollection;

    /**
     * Modify a field description to display it in the list view.
     */
    public function buildField(?string $type, FieldDescriptionInterface $fieldDescription): void;

    /**
     * Modify a field description and add it to the displayed columns.
     *
     * @param FieldDescriptionCollection<FieldDescriptionInterface> $list
     */
    public function addField(
        FieldDescriptionCollection $list,
        ?string $type,
        FieldDescriptionInterface $fieldDescription,
    ): void;
}
