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

use Sonata\AdminBundle\Admin\FieldDescription;
use Sonata\AdminBundle\Filter\Filter as BaseFilter;
use Symfony\Component\Form\Configurable;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

abstract class Filter extends BaseFilter
{

    public function apply($queryBuilder, $value)
    {
        $this->value = $value;

        $this->field->submit($value);

        list($alias, $field) = $this->association($queryBuilder, $this->field->getData());

        $this->filter($queryBuilder, $alias, $field, $this->field->getData());
    }

    protected function association($queryBuilder, $value)
    {
        if ($value && $this->description->getType() == ClassMetadataInfo::MANY_TO_MANY) {
            $queryBuilder->leftJoin(
                sprintf('%s.%s', $queryBuilder->getRootAlias(), $this->description->getFieldName()),
                $this->getName()
            );

            // todo : use the metadata information to find the correct column name
            return array($this->getName(), 'id');
        }

        return array($queryBuilder->getRootAlias(), $this->description->getFieldName());
    }
}