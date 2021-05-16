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

namespace Sonata\AdminBundle\Builder;

use Sonata\AdminBundle\FieldDescription\FieldDescriptionCollection;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface ListBuilderInterface extends BuilderInterface
{
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
        FieldDescriptionInterface $fieldDescription
    ): void;
}
