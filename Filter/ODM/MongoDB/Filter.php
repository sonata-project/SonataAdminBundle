<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Filter\ODM\MongoDB;

use Sonata\AdminBundle\Filter\Filter as BaseFilter;

abstract class Filter extends BaseFilter
{
    public function apply($queryBuilder, $value)
    {
        $this->value = $value;
        $this->filter($queryBuilder, null, $this->getFieldName(), $value);
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