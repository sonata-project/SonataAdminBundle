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

namespace Sonata\AdminBundle\Tests\App\Admin;

use Sonata\AdminBundle\Admin\BaseFieldDescription;

final class FieldDescription extends BaseFieldDescription
{
    public function setAssociationMapping($associationMapping): void
    {
    }

    public function getTargetEntity()
    {
        return null;
    }

    public function getTargetModel()
    {
        return null;
    }

    public function setFieldMapping($fieldMapping): void
    {
    }

    public function setParentAssociationMappings(array $parentAssociationMappings): void
    {
    }

    public function isIdentifier()
    {
        return false;
    }

    public function getValue($object)
    {
        return $this->getFieldValue($object, $this->fieldName);
    }
}
