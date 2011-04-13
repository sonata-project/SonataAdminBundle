<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) 2010-2011 Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\ModelManager\Mandango\Admin;

use Sonata\AdminBundle\Admin\BaseFieldDescription;

/**
 * MandangoFieldDescription.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
class MandangoFieldDescription extends BaseFieldDescription
{
    /**
     * {@inheritdoc}
     */
    public function setAssociationMapping($associationMapping)
    {
        if (!is_array($associationMapping)) {
           throw new \RuntimeException('The association mapping must be an array');
        }

        $this->associationMapping = $associationMapping;
    }

    /**
     * {@inheritdoc}
     */
    public function getTargetEntity()
    {
        if ($this->associationMapping) {
            return $this->associationMapping['targetEntity'];
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function setFieldMapping($fieldMapping)
    {
        if (!is_array($fieldMapping)) {
            throw new \RuntimeException('The field mapping must be an array');
        }

        $this->fieldMapping = $fieldMapping;

        $this->type         = $this->type ?: $fieldMapping['type'];
        $this->mappingType  = $this->mappingType ?: $fieldMapping['type'];
        $this->fieldName    = $this->fieldName ?: $fieldMapping['fieldName'];
    }

    /**
     * {@inheritdoc}
     */
    public function isIdentifier()
    {
        return false;
    }
}
