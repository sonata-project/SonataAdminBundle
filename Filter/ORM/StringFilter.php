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

class StringFilter extends Filter
{
    /**
     * @param Querybuilder $queryBuilder
     * @param string $alias
     * @param string $field
     * @param mixed $value
     * @return
     */
    public function filter($queryBuilder, $alias, $field, $value)
    {
        if ($value == null || strlen($value) == 0) {
            return;
        }

        // c.name LIKE '%word%' => c.name LIKE :fieldName
        $queryBuilder->andWhere(sprintf('%s.%s LIKE :%s', $alias, $field, $this->getName()));

        $queryBuilder->setParameter($this->getName(), sprintf($this->getOption('format'), $value));
    }

    /**
     * @return array
     */
    public function getDefaultOptions()
    {
        return array(
            'format'   => '%%%s%%'
        );
    }
}