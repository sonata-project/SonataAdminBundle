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
     * @param string $data
     * @return
     */
    public function filter($queryBuilder, $alias, $field, $data)
    {
        if (!$data || !is_array($data) || !array_key_exists('type', $data) || !array_key_exists('value', $data)) {
            return;
        }

        $operator = $this->getOperator((int) $data['type']);

        if (!$operator) {
            return;
        }

        // c.name > '1' => c.name OPERATOR :FIELDNAME
        $queryBuilder->andWhere(sprintf('%s.%s %s :%s', $alias, $field, $operator, $this->getName()));
        $queryBuilder->setParameter($this->getName(),  $data['value']);
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

    public function getRenderSettings()
    {
        return array('sonata_type_filter_number', array(
            'field_type'    => $this->getFieldType(),
            'field_options' => $this->getFieldOptions(),
        ));
    }
}