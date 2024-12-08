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

namespace Sonata\AdminBundle\Tests\Fixtures\FieldDescription;

use Sonata\AdminBundle\FieldDescription\BaseFieldDescription;

final class FieldDescription extends BaseFieldDescription
{
    public function getTargetEntity(): ?string
    {
        throw new \BadMethodCallException(\sprintf('Implement %s() method.', __METHOD__));
    }

    public function getTargetModel(): ?string
    {
        throw new \BadMethodCallException(\sprintf('Implement %s() method.', __METHOD__));
    }

    public function isIdentifier(): bool
    {
        throw new \BadMethodCallException(\sprintf('Implement %s() method.', __METHOD__));
    }

    public function getValue(object $object): never
    {
        throw new \BadMethodCallException(\sprintf('Implement %s() method.', __METHOD__));
    }

    public function describesSingleValuedAssociation(): bool
    {
        return false;
    }

    public function describesCollectionValuedAssociation(): bool
    {
        return false;
    }

    protected function setFieldMapping($fieldMapping): void
    {
        $this->fieldMapping = $fieldMapping;
    }

    protected function setAssociationMapping($associationMapping): void
    {
        $this->associationMapping = $associationMapping;
    }

    protected function setParentAssociationMappings(array $parentAssociationMappings): void
    {
        $this->parentAssociationMappings = $parentAssociationMappings;
    }
}
