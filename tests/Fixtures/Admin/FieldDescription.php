<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
namespace Sonata\AdminBundle\Tests\Fixtures\Admin;

use Sonata\AdminBundle\Admin\BaseFieldDescription;

class FieldDescription extends BaseFieldDescription
{
    public function setAssociationMapping($associationMapping)
    {
        // TODO: Implement setAssociationMapping() method.
    }

    public function getTargetEntity()
    {
        // TODO: Implement getTargetEntity() method.
    }

    public function setFieldMapping($fieldMapping)
    {
        // TODO: Implement setFieldMapping() method.
    }

    public function isIdentifier()
    {
        // TODO: Implement isIdentifier() method.
    }

    /**
     * set the parent association mappings information
     *
     * @param  array $parentAssociationMappings
     * @return void
     */
    public function setParentAssociationMappings(array $parentAssociationMappings)
    {
        // TODO: Implement setParentAssociationMappings() method.
    }

    /**
     * return the value linked to the description
     *
     * @param  $object
     * @return bool|mixed
     */
    public function getValue($object)
    {
        // TODO: Implement getValue() method.
    }
}
