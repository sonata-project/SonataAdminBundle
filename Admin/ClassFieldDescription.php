<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) 2010-2011 Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Admin;

class ClassFieldDescription extends BaseFieldDescription
{
    function __construct() {
        $this->name = 'class';
    }

    function setAssociationMapping($associationMapping)
    {
        // TODO: Implement setAssociationMapping() method.
    }

    function getTargetEntity()
    {
        // TODO: Implement getTargetEntity() method.
    }

    function setFieldMapping($fieldMapping)
    {
        // TODO: Implement setFieldMapping() method.
    }

    function setParentAssociationMappings(array $parentAssociationMappings)
    {
        // TODO: Implement setParentAssociationMappings() method.
    }

    function isIdentifier()
    {
        // TODO: Implement isIdentifier() method.
    }

    function getValue($object)
    {
        return array_search(get_class($object), $this->getOption('classes', array()));
    }
}
