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

use Sonata\AdminBundle\Form\Type\Filter\NumberType;

class NumberFilter extends Filter
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param string $alias
     * @param string $field
     * @param string $value
     * @return
     */
    public function filter($queryBuilder, $alias, $field, $value)
    {
        if ($value == null || !is_array($value)) {
            return;
        }

        $operator = $this->getOperator((int) $value['type']);

        if (!$operator) {
            return;
        }

        // c.name > '1' => c.name OPERATOR :FIELDNAME
        $queryBuilder->andWhere(sprintf('%s.%s %s :%s', $alias, $field, $operator, $this->getName()));
        $queryBuilder->setParameter($this->getName(),  $value['value']);
    }

    /**
     * @param $type
     * @return bool
     */
    private function getOperator($type)
    {
        $choices = array(
            NumberType::TYPE_EQUAL            => '=',
            NumberType::TYPE_GREATER_EQUAL    => '>=',
            NumberType::TYPE_GREATER_THAN     => '>',
            NumberType::TYPE_LESS_EQUAL       => '<=',
            NumberType::TYPE_LESS_THAN        => '<',
        );

        return isset($choices[$type]) ? $choices[$type] : false;
    }
}