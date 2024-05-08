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

namespace Sonata\AdminBundle\Tests\App\FieldDescription;

use Sonata\AdminBundle\FieldDescription\BaseFieldDescription;

final class FieldDescription extends BaseFieldDescription
{
    public function setAssociationMapping($associationMapping): void
    {
    }

    public function getTargetModel(): ?string
    {
        return null;
    }

    public function setFieldMapping(array $fieldMapping): void
    {
    }

    public function setParentAssociationMappings(array $parentAssociationMappings): void
    {
    }

    public function isIdentifier(): bool
    {
        return false;
    }

    public function getValue(object $object): mixed
    {
        return $this->getFieldValue($object, $this->fieldName);
    }

    public function describesSingleValuedAssociation(): bool
    {
        return false;
    }

    public function describesCollectionValuedAssociation(): bool
    {
        return false;
    }
}
