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

namespace Sonata\AdminBundle\Tests\Fixtures\Admin;

use Sonata\AdminBundle\Admin\BaseFieldDescription;

class FieldDescription extends BaseFieldDescription
{
    public function setAssociationMapping($associationMapping): void
    {
        // TODO: Implement setAssociationMapping() method.
    }

    public function getTargetEntity(): ?string
    {
        // TODO: Implement getTargetEntity() method.
    }

    public function setFieldMapping($fieldMapping): void
    {
        // TODO: Implement setFieldMapping() method.
    }

    public function isIdentifier(): bool
    {
        // TODO: Implement isIdentifier() method.
    }

    /**
     * set the parent association mappings information.
     *
     * @param array $parentAssociationMappings
     */
    public function setParentAssociationMappings(array $parentAssociationMappings): void
    {
        // TODO: Implement setParentAssociationMappings() method.
    }

    /**
     * return the value linked to the description.
     *
     * @param  $object
     *
     * @return bool|mixed
     */
    public function getValue($object)
    {
        // TODO: Implement getValue() method.
    }
}
