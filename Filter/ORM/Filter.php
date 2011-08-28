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
        return array($queryBuilder->getRootAlias(), $this->getFieldName());
    }

    protected function applyWhere($queryBuilder, $parameter)
    {
        if ($this->getCondition() == self::CONDITION_OR) {
            $queryBuilder->orWhere($parameter);
        } else {
            $queryBuilder->andWhere($parameter);
        }
    }
}