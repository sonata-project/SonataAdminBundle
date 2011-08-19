<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Filter\ORM;

use Sonata\AdminBundle\Filter\Filter as BaseFilter;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Symfony\Component\Form\FormBuilder;

abstract class Filter extends BaseFilter
{
    public function apply($queryBuilder, $value)
    {
        $this->value = $value;

        list($alias, $field) = $this->association($queryBuilder, $value);

        $this->filter($queryBuilder, $alias, $field, $value);
    }

    protected function association($queryBuilder, $value)
    {
        if ($value && $this->getFieldDescription()->getMappingType() == ClassMetadataInfo::MANY_TO_MANY) {
            $queryBuilder->leftJoin(
                sprintf('%s.%s', $queryBuilder->getRootAlias(), $this->getFieldDescription()->getFieldName()),
                $this->getName()
            );

            // todo : use the metadata information to find the correct column name
            return array($this->getName(), 'id');
        }

        return array($queryBuilder->getRootAlias(), $this->getFieldDescription()->getFieldName());
    }
}