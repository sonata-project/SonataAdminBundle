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

use Sonata\AdminBundle\Form\Type\Filter\StringType;

class StringFilter extends Filter
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
        if (!is_array($value)) {
            return;
        }

        $value['text'] = trim($value['text']);

        if (strlen($value['text']) == 0) {
            return;
        }

        $operator = $this->getOperator((int) $value['type']);

        if (!$operator) {
            $operator = 'LIKE';
        }

        // c.name > '1' => c.name OPERATOR :FIELDNAME
        $queryBuilder->andWhere(sprintf('%s.%s %s :%s', $alias, $field, $operator, $this->getName()));

        if ($value['type'] == StringType::TYPE_EQUAL) {
            $queryBuilder->setParameter($this->getName(), $value['text']);
        } else {
            $queryBuilder->setParameter($this->getName(), sprintf($this->getOption('format'), $value['text']));
        }
    }

    /**
     * @param $type
     * @return bool
     */
    private function getOperator($type)
    {
        $choices = array(
            StringType::TYPE_CONTAINS         => 'LIKE',
            StringType::TYPE_NOT_CONTAINS     => 'NOT LIKE',
            StringType::TYPE_EQUAL            => '=',
        );

        return isset($choices[$type]) ? $choices[$type] : false;
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