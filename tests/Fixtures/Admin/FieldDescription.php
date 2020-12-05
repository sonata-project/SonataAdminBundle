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
    // NEXT_MAJOR: Make this method protected.
    public function setAssociationMapping($associationMapping): void
    {
        $this->associationMapping = $associationMapping;
    }

    public function getTargetEntity(): void
    {
        throw new \BadMethodCallException(sprintf('Implement %s() method.', __METHOD__));
    }

    public function getTargetModel(): ?string
    {
        throw new \BadMethodCallException(sprintf('Implement %s() method.', __METHOD__));
    }

    // NEXT_MAJOR: Make this method protected.
    public function setFieldMapping($fieldMapping): void
    {
        $this->fieldMapping = $fieldMapping;
    }

    public function isIdentifier(): void
    {
        throw new \BadMethodCallException(sprintf('Implement %s() method.', __METHOD__));
    }

    /**
     * NEXT_MAJOR: Make this method protected.
     *
     * @param array $parentAssociationMappings
     */
    public function setParentAssociationMappings(array $parentAssociationMappings): void
    {
        $this->parentAssociationMappings = $parentAssociationMappings;
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
        throw new \BadMethodCallException(sprintf('Implement %s() method.', __METHOD__));
    }
}
